<?php
use BetaKiller\Error\ExceptionHandler;
use BetaKiller\Exception\ExceptionHandlerInterface;
use BetaKiller\ExceptionInterface;

class Kohana_Exception extends Kohana_Kohana_Exception implements ExceptionInterface
{
    /**
     * @var ExceptionHandlerInterface
     */
    private static $exceptionHandler;

    public function __construct($message = '', array $variables = null, $code = 0, Throwable $previous = null)
    {
        // Set up default message text if it was not set
        $message = $message ?: $this->getDefaultMessageI18nKey();

        parent::__construct($message, $variables, $code, $previous);
    }

    public static function setHandler(ExceptionHandlerInterface $handler)
    {
        self::$exceptionHandler = $handler;
    }

    /**
     * Inline exception handler, displays the error message, source of the
     * exception, and the stack trace of the error.
     *
     * @uses    Kohana_Exception::response
     *
     * @param   \Throwable $e
     */
    public static function handler(Throwable $e)
    {
        $exitCode = 1;

        $response = self::_handler($e);

        if (PHP_SAPI === 'cli') {
            $exitCode = $e->getCode();

            // Never exit "0" after an exception.
            if ($exitCode === 0) {
                $exitCode = 1;
            }
        } else {
            // Send headers to the browser
            $response->send_headers();
        }

        // Send the response to the browser or cli
        echo $response->send_headers()->body();

        exit($exitCode);
    }

    /**
     * @param Throwable $exception
     *
     * @return \Response
     */
    public static function _handler(Throwable $exception)
    {
        // Use default Kohana handler as fallback
        if (!self::$exceptionHandler) {
            return parent::_handler($exception);
        }

        try {
            return self::$exceptionHandler->handle($exception);
        } catch (\Throwable $e) {
            self::log($e);
            return parent::_handler($exception);
        }
    }

    /**
     * Returns default message for current exception
     * Allows throwing concrete exception without message
     * Useful for custom exception types
     *
     * @return string
     */
    public function getDefaultMessageI18nKey(): string
    {
        return ExceptionHandler::getErrorLabelI18nKey($this);
    }

    /**
     * Returns TRUE if someone must be notified about current exception type
     * Override this method with *false* return if notification about exceptions of concrete class is not needed
     *
     * @return bool
     */
    public function isNotificationEnabled(): bool
    {
        return true;
    }

    /**
     * If returns true, then original exception message will be shown to end-user in JSON and error pages
     * Override this method with *true* return if it's domain exception
     *
     * @return bool
     */
    public function showOriginalMessageToUser(): bool
    {
        return false;
    }

    /**
     * Overwrite this method with "return TRUE" to show custom message in all cases
     * Override this method with *true* return if this exception type has dedicated error page like 404
     *
     * @return bool
     */
    public function alwaysShowNiceMessage(): bool
    {
        return false;
    }
}
