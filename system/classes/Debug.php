<?php

use BetaKiller\Exception\HttpExceptionInterface;

class Debug extends Kohana_Debug
{
    public const CSP_SCRIPT = "'sha256-MJUqfrFhbQiIAwiogLLNFfJy62oDe3Wi5DCrVRCYbNg='";
    public const CSP_STYLE  = "'sha256-9rVpVA6gIB/WMJ0yNaDAuF8Wo0ScZQPhHQCQVIbsqWE='";

    /**
     * @var  array  PHP error code => human readable name
     */
    public static $phpErrors = [
        E_ERROR             => 'Fatal Error',
        E_USER_ERROR        => 'User Error',
        E_PARSE             => 'Parse Error',
        E_WARNING           => 'Warning',
        E_USER_WARNING      => 'User Warning',
        E_STRICT            => 'Strict',
        E_NOTICE            => 'Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED        => 'Deprecated',
    ];

    public static function htmlStacktrace(\Throwable $e): string
    {
        try {
            if (!\interface_exists(\BetaKiller\View\ViewInterface::class)) {
                return Kohana_Kohana_Exception::text($e);
            }

            // Get the exception information
            $previous = $e->getPrevious();
            $class    = get_class($e);
            $code     = $e->getCode();
            $message  = $e->getMessage();
            $file     = $e->getFile();
            $line     = $e->getLine();
            $trace    = $previous ? $previous->getTrace() : $e->getTrace();

            /**
             * HTTP_Exceptions are constructed in the HTTP_Exception::factory()
             * method. We need to remove that entry from the trace and overwrite
             * the variables from above.
             */
            if ($e instanceof HttpExceptionInterface && $trace[0]['function'] === 'factory') {
                extract(array_shift($trace), EXTR_OVERWRITE);
            }

            if ($e instanceof ErrorException) {
                /**
                 * If XDebug is installed, and this is a fatal error,
                 * use XDebug to generate the stack trace
                 */
                if (function_exists('xdebug_get_function_stack') && $code === E_ERROR) {
                    $trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);

                    foreach ($trace as & $frame) {
                        /**
                         * XDebug pre 2.1.1 doesn't currently set the call type key
                         * http://bugs.xdebug.org/view.php?id=695
                         */
                        if (!isset($frame['type'])) {
                            $frame['type'] = '??';
                        }

                        // XDebug also has a different name for the parameters array
                        if (isset($frame['params']) && !isset($frame['args'])) {
                            $frame['args'] = $frame['params'];
                        }
                    }
                }

                if (isset(self::$phpErrors[$code])) {
                    // Use the human-readable error name
                    $code = self::$phpErrors[$code];
                }
            }

            /**
             * The stack trace becomes unmanageable inside PHPUnit.
             *
             * The error view ends up several GB in size, taking
             * several minutes to render.
             */
            if (defined('PHPUnit_MAIN_METHOD')) {
                $trace = array_slice($trace, 0, 2);
            }

            // Instantiate the error view.
            $view = View::factory('kohana/error');

            foreach (get_defined_vars() as $varName => $varValue) {
                $view->set($varName, $varValue);
            }

            // Set the response body
            return $view->render();
        } catch (Throwable $e) {
            return Kohana_Kohana_Exception::text($e);
        }
    }
}
