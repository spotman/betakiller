<?php

use BetaKiller\Exception\ExceptionHandlerInterface;

class Kohana_Exception extends Kohana_Kohana_Exception
{
    /**
     * @var ExceptionHandlerInterface
     */
    private static $exceptionHandler;

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
    public static function _handler(Throwable $exception): \Response
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
}
