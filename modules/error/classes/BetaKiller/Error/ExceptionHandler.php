<?php
namespace BetaKiller\Error;

use BetaKiller\Exception;
use BetaKiller\Exception\ExceptionHandlerInterface;
use BetaKiller\Exception\HttpExceptionExpectedInterface;
use BetaKiller\Exception\HttpExceptionInterface;
use BetaKiller\ExceptionInterface;
use BetaKiller\Helper\AppEnv;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\IFace\AbstractHttpErrorIFace;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\View\IFaceView;
use Psr\Log\LoggerInterface;
use Response;

class ExceptionHandler implements ExceptionHandlerInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $ifaceProvider;

    /**
     * @var \BetaKiller\Helper\AppEnv
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
     * Exception counter for preventing recursion
     */
    private static $counter = 0;

    /**
     * ExceptionHandler constructor.
     *
     * @param \BetaKiller\Helper\AppEnv       $appEnv
     * @param \BetaKiller\IFace\IFaceProvider $ifaceProvider
     * @param \BetaKiller\View\IFaceView      $ifaceView
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(
        AppEnv $appEnv,
        IFaceProvider $ifaceProvider,
        IFaceView $ifaceView,
        LoggerInterface $logger
    ) {
        $this->appEnv        = $appEnv;
        $this->logger        = $logger;
        $this->ifaceProvider = $ifaceProvider;
        $this->ifaceView     = $ifaceView;
    }

    /**
     * @param \Throwable $exception
     *
     * @return \Response
     */
    public function handle(\Throwable $exception): Response
    {
        self::$counter++;

        if (self::$counter > 10) {
            $this->logger->alert('Too much exceptions (recursion, see below)');
            $this->logException($this->logger, $exception);
            die();
        }

        $notify = ($exception instanceof ExceptionInterface)
            ? $exception->isNotificationEnabled()
            : true;

        if ($notify) {
            // Logging exception
            $this->logException($this->logger, $exception);
        }

        if ($this->appEnv->isCLI()) {
            // Force exception message to be shown even if notification is disabled
            if (!$notify) {
                echo $exception->getMessage().PHP_EOL.$exception->getTraceAsString();
            }

            // CLI log handler already printed the message, return empty response
            return new Response;
        }

        // Make nice message if allowed or use default Kohana response
        $response = $this->makeNiceMessage($exception) ?: $this->makeDebugResponse($exception);

        self::$counter--;

        return $response;
    }

    /**
     * Returns user-friendly exception page
     *
     * @param \Throwable $exception
     *
     * @return \Response|null
     */
    private function makeNiceMessage(\Throwable $exception): ?Response
    {
        // Prevent displaying custom error pages for expected exceptions (301, 302, etc)
        if (($exception instanceof HttpExceptionExpectedInterface) && !$exception->alwaysShowNiceMessage()) {
            return $exception->get_response();
        }

        $alwaysShowNiceMessage = ($exception instanceof ExceptionInterface)
            ? $exception->alwaysShowNiceMessage()
            : false;

        $isDebug = $this->appEnv->isDebugEnabled();

        if (!$alwaysShowNiceMessage && $isDebug) {
            return null;
        }

        $response = Response::factory();
        $httpCode = $this->getErrorHttpCode($exception);

        try {
            $iface = $this->getErrorIFaceForCode($httpCode);

            $body = $iface
                ? $this->ifaceView->render($iface)
                : $this->renderFallbackMessage($exception);

            $response->status($httpCode)->body($body);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            if ($isDebug) {
                $response = $this->makeDebugResponse($e);
            }

            $response->status(500);
        }

        return $response;
    }

    /**
     * Returns developer-friendly exception page
     *
     * @param \Throwable $exception
     *
     * @return \Response
     */
    private function makeDebugResponse(\Throwable $exception): Response
    {
        return \Kohana_Exception::response($exception);
    }

    /**
     * @param int $code
     *
     * @return \BetaKiller\IFace\AbstractHttpErrorIFace|null
     */
    private function getErrorIFaceForCode(int $code): ?AbstractHttpErrorIFace
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
     * @return \BetaKiller\IFace\AbstractHttpErrorIFace|null
     */
    private function createErrorIFaceFromCode(int $code): ?AbstractHttpErrorIFace
    {
        try {
            return $this->createIFaceFromCodename('HttpError'.$code);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            return null;
        }
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\AbstractHttpErrorIFace|mixed
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function createIFaceFromCodename(string $codename): ?AbstractHttpErrorIFace
    {
        return $this->ifaceProvider->fromCodename($codename);
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
    public function getExceptionMessage(\Throwable $e): string
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

    private function getErrorLabelI18nKey(\Throwable $e)
    {
        $code = $this->getErrorHttpCode($e);

        return $this->getLabelI18nKeyForHttpCode($code);
    }

    private function getErrorHttpCode(\Throwable $e)
    {
        $code = $e->getCode();

        return (($e instanceof HttpExceptionInterface) && $code)
            ? $code
            : ExceptionInterface::DEFAULT_EXCEPTION_CODE;
    }

    private function getLabelI18nKeyForHttpCode(int $code)
    {
        return 'error.http.'.$code.'.label';
    }
}
