<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Exception;
use BetaKiller\Exception\HttpExceptionInterface;
use BetaKiller\ExceptionInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceProvider;
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
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $ifaceProvider;

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
     * ErrorPageMiddleware constructor.
     *
     * @param \BetaKiller\IFace\IFaceProvider    $ifaceProvider
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \BetaKiller\View\IFaceView         $ifaceView
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(
        IFaceProvider $ifaceProvider,
        AppEnvInterface $appEnv,
        IFaceView $ifaceView,
        LoggerInterface $logger
    ) {
        $this->ifaceProvider = $ifaceProvider;
        $this->appEnv        = $appEnv;
        $this->ifaceView     = $ifaceView;
        $this->logger        = $logger;
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
            return $this->handleException($request, $e);
        }
    }

    private function handleException(ServerRequestInterface $request, \Throwable $e): ResponseInterface
    {
        $notify = ($e instanceof ExceptionInterface)
            ? $e->isNotificationEnabled()
            : true;

        if ($notify) {
            // Logging exception
            $this->logException($this->logger, $e);
        }

//        if ($this->appEnv->isCLI()) {
//            // Force exception message to be shown even if notification is disabled
//            if (!$notify) {
//                echo $e->getMessage().PHP_EOL.$e->getTraceAsString();
//            }
//
//            // CLI log handler already printed the message, return empty response
//            return new EmptyResponse;
//        }

        if (ServerRequestHelper::isJsonPreferred($request)) {
            return $this->makeJsonResponse($e);
        }

        // Make nice message if allowed or use default Kohana response
        return $this->makeNiceMessage($e, $request) ?: $this->makeDebugResponse($e);
    }

    /**
     * Returns JSON response
     *
     * @param \Throwable $e
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function makeJsonResponse(\Throwable $e): ResponseInterface
    {
        if (!$e instanceof ExceptionInterface || !$e->showOriginalMessageToUser()) {
            // No messages for custom exceptions
            return ResponseHelper::errorJson();
        }

        $message = $e->getMessage() ?: __($e->getDefaultMessageI18nKey());

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

        $httpCode = $this->getErrorHttpCode($exception);

        try {
            $iface = $this->getErrorIFaceForCode($httpCode);

            $body = $iface
                ? $this->ifaceView->render($iface, $request)
                : $this->renderFallbackMessage($exception);

            return new HtmlResponse($body, $httpCode);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            return $isDebug
                ? $this->makeDebugResponse($e)
                : ResponseHelper::text('Error', $httpCode);
        }
    }

    /**
     * Returns developer-friendly exception page
     *
     * @param \Throwable $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function makeDebugResponse(\Throwable $exception): ResponseInterface
    {
        $stacktrace = \Debug::htmlStacktrace($exception);

        return ResponseHelper::html($stacktrace, 500);
    }

    /**
     * @param int $code
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    private function getErrorIFaceForCode(int $code): ?IFaceInterface
    {
        // Try to find IFace provided code first and use default IFace if failed
        foreach ([$code, ExceptionInterface::DEFAULT_EXCEPTION_CODE] as $tryCode) {
            $iface = $this->createErrorIFaceFromCode($tryCode);

            if ($iface) {
                return $iface;
            }
        }

        return null;
    }

    /**
     * @param int $code
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    private function createErrorIFaceFromCode(int $code): ?IFaceInterface
    {
        try {
            return $this->ifaceProvider->fromCodename('HttpError'.$code);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            return null;
        }
    }

    /**
     * @param \Throwable $e
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    private function renderFallbackMessage(\Throwable $e): string
    {
        $message = $this->getExceptionMessage($e);

        // Prevent XSS
        return htmlspecialchars($message, ENT_QUOTES);
    }

    /**
     * Returns text which would be shown to user on uncaught exception
     * For most of exception classes it returns default label (we do not want to inform user about our problems)
     *
     * @param \Throwable $e
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    private function getExceptionMessage(\Throwable $e): string
    {
        $showOriginalMessage = ($e instanceof ExceptionInterface) && $e->showOriginalMessageToUser();

        return $showOriginalMessage
            ? $this->getOriginalMessage($e)
            : $this->getMaskedMessage($e);
    }

    /**
     * @param \Throwable $e
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    private function getOriginalMessage(\Throwable $e): string
    {
        $message = $e->getMessage();

        // Return message if exists
        if ($message) {
            return $message;
        }

        // Use default message if defined
        $i18nKey = ($e instanceof ExceptionInterface) ? $e->getDefaultMessageI18nKey() : null;

        // Http exceptions may omit message and will use default label instead
        if (!$i18nKey && $e instanceof HttpExceptionInterface) {
            $i18nKey = $this->getErrorLabelI18nKey($e);
        }

        if (!$i18nKey) {
            throw new Exception('Exception :class must provide message in constructor or define default message', [
                ':class' => \get_class($e),
            ]);
        }

        return __($i18nKey);
    }

    private function getMaskedMessage(\Throwable $e): string
    {
        $key = $this->getErrorLabelI18nKey($e);

        return __($key);
    }

    private function getErrorLabelI18nKey(\Throwable $e): string
    {
        $code = $this->getErrorHttpCode($e);

        return $this->getLabelI18nKeyForHttpCode($code);
    }

    private function getErrorHttpCode(\Throwable $e): int
    {
        $code = $e->getCode();

        return (($e instanceof HttpExceptionInterface) && $code)
            ? $code
            : ExceptionInterface::DEFAULT_EXCEPTION_CODE;
    }

    private function getLabelI18nKeyForHttpCode(int $code): string
    {
        return 'error.http.'.$code.'.label';
    }
}