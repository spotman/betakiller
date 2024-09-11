<?php

use BetaKiller\Exception\HttpExceptionInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\View\ViewInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Debug extends Kohana_Debug
{
    private const CSP_SCRIPT = "'sha256-hpWlm9GZYX03IbtbGSnSyCu7X5O09yaWXkWeH4uBsQY='";
    private const CSP_STYLE  = "'sha256-5s3aYV/2O0uyfXhVtKm6S506HBIzJJCp7Mt/DQQlx8c='";

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

    public static function renderStackTrace(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        \Debug::injectStackTraceCsp($request);

        return new HtmlResponse(\Debug::htmlStackTrace($e, $request), 500);
    }

    public static function htmlStackTrace(Throwable $e, ServerRequestInterface $request = null): string
    {
        try {
            if (!\interface_exists(ViewInterface::class)) {
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

    public static function injectStackTraceCsp(ServerRequestInterface $request): void
    {
        // CSP may be disabled
        $csp = ServerRequestHelper::getCsp($request);

        $csp?->csp('script', self::CSP_SCRIPT);
        $csp?->csp('style', self::CSP_STYLE);
    }
}
