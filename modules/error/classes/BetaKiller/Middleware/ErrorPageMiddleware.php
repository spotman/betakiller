<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Error\ExceptionService;
use BetaKiller\ExceptionInterface;
use BetaKiller\Factory\IFaceFactory;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractHttpErrorIFace;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\View\IFaceView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\HtmlResponse;

class ErrorPageMiddleware implements MiddlewareInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\View\IFaceView
     */
    private $ifaceView;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Factory\IFaceFactory
     */
    private $ifaceFactory;

    /**
     * @var \BetaKiller\Error\ExceptionService
     */
    private $exceptionService;

    /**
     * ErrorPageMiddleware constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Factory\IFaceFactory        $ifaceFactory
     * @param \BetaKiller\Helper\AppEnvInterface      $appEnv
     * @param \BetaKiller\View\IFaceView              $ifaceView
     * @param \BetaKiller\Error\ExceptionService      $exceptionService
     * @param \Psr\Log\LoggerInterface                $logger
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        IFaceFactory $ifaceFactory,
        AppEnvInterface $appEnv,
        IFaceView $ifaceView,
        ExceptionService $exceptionService,
        LoggerInterface $logger
    ) {
        $this->appEnv           = $appEnv;
        $this->ifaceView        = $ifaceView;
        $this->tree             = $tree;
        $this->ifaceFactory     = $ifaceFactory;
        $this->exceptionService = $exceptionService;
        $this->logger           = $logger;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            // Logging exception
            $this->logException($this->logger, $e, $request);

            return $this->handleException($request, $e);
        }
    }

    private function handleException(ServerRequestInterface $request, \Throwable $e): ResponseInterface
    {
        if (ServerRequestHelper::isJsonPreferred($request)) {
            return $this->makeJsonResponse($e, $request);
        }

        // Make nice message if allowed or use default Kohana response
        return $this->makeNiceMessage($e, $request) ?: $this->makeDebugResponse($e, $request);
    }

    /**
     * Returns JSON response
     *
     * @param \Throwable                               $e
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception
     */
    private function makeJsonResponse(\Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $lang = $this->getRequestLang($request);

        $message = $this->exceptionService->getExceptionMessage($e, $lang);

        return ResponseHelper::errorJson($message);
    }

    /**
     * Returns user-friendly exception page
     *
     * @param \Throwable                               $exception
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    private function makeNiceMessage(\Throwable $exception, ServerRequestInterface $request): ?ResponseInterface
    {
        $alwaysShowNiceMessage = ($exception instanceof ExceptionInterface)
            ? $exception->alwaysShowNiceMessage()
            : false;

        $isDebug = $this->appEnv->isDebugEnabled();

        if (!$alwaysShowNiceMessage && $isDebug) {
            return null;
        }

        $httpCode = $this->exceptionService->getHttpCode($exception);

        $stack = ServerRequestHelper::getUrlElementStack($request);
        $last  = $stack->hasCurrent() ? $stack->getCurrent() : null;

        try {
            $iface = $this->getErrorIFaceForCode($httpCode, $last);

            if ($iface instanceof AbstractHttpErrorIFace) {
                $iface->setException($exception);
            }

            $body = $iface
                ? $this->ifaceView->render($iface, $request)
                : $this->renderFallbackMessage($exception, $request);

            return new HtmlResponse($body, $httpCode);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            return $isDebug
                ? $this->makeDebugResponse($e, $request)
                : ResponseHelper::text('Error', $httpCode);
        }
    }

    /**
     * Returns developer-friendly exception page
     *
     * @param \Throwable                               $exception
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function makeDebugResponse(\Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        \Debug::injectStackTraceCsp($request);

        $stacktrace = \Debug::htmlStacktrace($exception, $request);

        return ResponseHelper::html($stacktrace, 500);
    }

    /**
     * @param int                                      $code
     * @param \BetaKiller\Url\UrlElementInterface|null $lastElement
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getErrorIFaceForCode(int $code, ?UrlElementInterface $lastElement): ?IFaceInterface
    {
        // Try to find IFace provided code first and use default IFace if failed
        foreach ([$code, ExceptionService::DEFAULT_HTTP_CODE] as $tryCode) {
            $model = $this->searchErrorIFaceInBranch($tryCode, $lastElement);

            if ($model) {
                return $this->ifaceFactory->createFromUrlElement($model);
            }
        }

        return null;
    }

    /**
     * @param int                                      $code
     * @param \BetaKiller\Url\UrlElementInterface|null $parent
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    private function searchErrorIFaceInBranch(int $code, ?UrlElementInterface $parent): ?IFaceModelInterface
    {
        try {
            // Try to find dedicated IFace first
            do {
                $layer = $parent
                    ? $this->tree->getChildren($parent)
                    : $this->tree->getRoot();

                foreach ($layer as $item) {
                    if (!$item instanceof IFaceModelInterface) {
                        continue;
                    }

                    if (strpos($item->getCodename(), 'Error'.$code) !== false) {
                        return $item;
                    }
                }

                if ($parent) {
                    $parent = $this->tree->getParent($parent);
                }
            } while ($parent);

            // Use default IFace if failed
            $codename = 'HttpError'.$code;

            if ($this->tree->has($codename)) {
                $item = $this->tree->getByCodename($codename);

                if ($item instanceof IFaceModelInterface) {
                    return $item;
                }
            }
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);
        }

        return null;
    }

    /**
     * @param \Throwable                               $e
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    private function renderFallbackMessage(\Throwable $e, ServerRequestInterface $request): string
    {
        $lang = $this->getRequestLang($request);

        $message = $this->exceptionService->getExceptionMessage($e, $lang);

        // Prevent XSS
        return htmlspecialchars($message, ENT_QUOTES);
    }

    private function getRequestLang(ServerRequestInterface $request): ?LanguageInterface
    {
        return ServerRequestHelper::hasI18n($request)
            ? ServerRequestHelper::getI18n($request)->getLang()
            : null;
    }
}
