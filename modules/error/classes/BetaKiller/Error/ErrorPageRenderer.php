<?php

declare(strict_types=1);

namespace BetaKiller\Error;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\ExceptionInterface;
use BetaKiller\Factory\IFaceFactory;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractHttpErrorIFace;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\View\IFaceRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class ErrorPageRenderer implements ErrorPageRendererInterface
{
    /**
     * ErrorPageRenderer constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Factory\IFaceFactory        $ifaceFactory
     * @param \BetaKiller\Env\AppEnvInterface         $appEnv
     * @param \BetaKiller\View\IFaceRendererInterface $ifaceRenderer
     * @param \BetaKiller\Error\ExceptionService      $exceptionService
     * @param \Psr\Log\LoggerInterface                $logger
     */
    public function __construct(
        private UrlElementTreeInterface $tree,
        private IFaceFactory $ifaceFactory,
        private AppEnvInterface $appEnv,
        private IFaceRendererInterface $ifaceRenderer,
        private ExceptionService $exceptionService,
        private LoggerInterface $logger
    ) {
    }

    public function render(ServerRequestInterface $request, Throwable $e): ResponseInterface
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
    private function makeJsonResponse(Throwable $e, ServerRequestInterface $request): ResponseInterface
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
    private function makeNiceMessage(Throwable $exception, ServerRequestInterface $request): ?ResponseInterface
    {
        $alwaysShowNiceMessage = ($exception instanceof ExceptionInterface)
            ? $exception->alwaysShowNiceMessage()
            : false;

        $isDebug = $this->appEnv->isDebugEnabled();

        if (!$alwaysShowNiceMessage && $isDebug) {
            return null;
        }

        $httpCode = $this->exceptionService->getHttpCode($exception);

        try {
            $iface = $this->getErrorIFaceForCode($httpCode);

            if (!$iface) {
                return ResponseHelper::text($this->renderFallbackMessage($exception, $request), $httpCode);
            }

            if ($iface instanceof AbstractHttpErrorIFace) {
                $request = AbstractHttpErrorIFace::injectException($request, $exception);
            }

            return $this->ifaceRenderer->render($iface, $request)->withStatus($httpCode);
        } catch (Throwable $e) {
            LoggerHelper::logRequestException($this->logger, $e, $request);

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
    private function makeDebugResponse(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        return \Debug::renderStackTrace($exception, $request);
    }

    /**
     * @param int $code
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getErrorIFaceForCode(int $code): ?IFaceInterface
    {
        // Try to find IFace provided code first and use default IFace if failed
        foreach ([$code, ExceptionService::DEFAULT_HTTP_CODE] as $tryCode) {
            $model = $this->findErrorIFace($tryCode);

            if ($model) {
                return $this->ifaceFactory->createFromUrlElement($model);
            }
        }

        return null;
    }

    /**
     * @param int $code
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    private function findErrorIFace(int $code): ?IFaceModelInterface
    {
        try {
            $codename = 'HttpError'.$code;

            if ($this->tree->has($codename)) {
                $item = $this->tree->getByCodename($codename);

                if ($item instanceof IFaceModelInterface) {
                    return $item;
                }
            }
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
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
    private function renderFallbackMessage(Throwable $e, ServerRequestInterface $request): string
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
