<?php
namespace BetaKiller\Error;


use BetaKiller\Exception\ExceptionHandlerInterface;
use BetaKiller\Exception\HttpExceptionExpectedInterface;
use BetaKiller\Exception\HttpExceptionInterface;
use BetaKiller\ExceptionInterface;
use BetaKiller\Helper\AppEnv;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\IFace\AbstractHttpErrorIFace;
use BetaKiller\IFace\IFaceFactory;
use Psr\Log\LoggerInterface;
use Response;

class ExceptionHandler implements ExceptionHandlerInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\IFace\IFaceFactory
     */
    private $ifaceFactory;

    /**
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Exception counter for preventing recursion
     */
    private static $_counter = 0;

    /**
     * ExceptionHandler constructor.
     *
     * @param \BetaKiller\Helper\AppEnv      $appEnv
     * @param \BetaKiller\IFace\IFaceFactory $ifaceFactory
     * @param \Psr\Log\LoggerInterface       $logger
     */
    public function __construct(AppEnv $appEnv, IFaceFactory $ifaceFactory, LoggerInterface $logger)
    {
        $this->ifaceFactory = $ifaceFactory;
        $this->appEnv = $appEnv;
        $this->logger = $logger;
    }

    public function handle(\Throwable $exception): Response
    {
        static::$_counter++;

        if (static::$_counter > 10) {
            $this->logger->alert('Too much exceptions (recursion)');
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

        static::$_counter--;

        return $response;
    }

    /**
     * Возвращает контент красивого сообщения об ошибке
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

        if (!$alwaysShowNiceMessage && !$this->appEnv->inProduction(true)) {
            return null;
        }

        // Если это не наследник Kohana_Exception, оборачиваем его, чтобы показать базовое сообщение об ошибке
//        if (!($exception instanceof Kohana_Exception)) {
//            $exception = new Kohana_Exception(':error', [':error' => $exception->getMessage()], $exception->getCode(), $exception);
//        }

        $response = Response::factory();
        $httpCode = self::getHttpErrorCode($exception);

        try {
            $iface = $this->getErrorIFaceForCode($httpCode);

            $body = $iface
                ? $iface->setException($exception)->render()
                : $this->renderDefaultMessage($exception);

            $response->status($httpCode)->body($body);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);
            $response->status(500);
        }

        return $response;
    }

    private function makeDebugResponse(\Throwable $exception): Response
    {
        return \Kohana_Exception::response($exception);
    }

    private function getErrorIFaceForCode(int $code): ?AbstractHttpErrorIFace
    {
        // Try to find IFace provided code first and use default IFace if failed
        foreach ([$code, ExceptionInterface::DEFAULT_EXCEPTION_CODE] as $tryCode) {
            if ($iface = $this->createErrorIFaceFromCode($tryCode)) {
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
        return $this->ifaceFactory->fromCodename($codename);
    }

    private function renderDefaultMessage(\Throwable $e): string
    {
        if ($userMessage = $this->getUserMessage($e)) {
            // Prevent XSS
            return htmlspecialchars($userMessage, ENT_QUOTES);
        }

        $key = self::getErrorLabelI18nKey($e);

        return __($key);
    }

    /**
     * Returns text which would be shown to user on uncaught exception
     * For most of exception classes it returns *null* (we do not want to inform user about our problems)
     *
     * @param \Throwable $e
     *
     * @return null|string
     */
    public function getUserMessage(\Throwable $e): ?string
    {
        $show = ($e instanceof ExceptionInterface) && $e->showOriginalMessageToUser();

        return $show ? $e->getMessage() : null;
    }

    public static function getErrorLabelI18nKey(\Throwable $e)
    {
        $code = static::getHttpErrorCode($e);

        return static::getLabelI18nKeyForCode($code);
    }

    private static function getHttpErrorCode(\Throwable $e)
    {
        return ($e instanceof HttpExceptionInterface)
            ? $e->getCode()
            : ExceptionInterface::DEFAULT_EXCEPTION_CODE;
    }

    private static function getLabelI18nKeyForCode(int $code)
    {
        return 'error.'.$code.'.label';
    }
}
