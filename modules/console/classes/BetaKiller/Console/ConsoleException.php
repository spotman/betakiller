<?php namespace BetaKiller\Console;

defined('SYSPATH') or die('No direct script access.');

use BetaKiller\Exception;
use Debug;
use ORM_Validation_Exception;
use Throwable;

/**
 * Minion exception
 *
 * @package        Kohana
 * @category       Minion
 * @author         Kohana Team
 * @copyright  (c) 2009-2011 Kohana Team
 * @license        http://kohanaframework.org/license
 */
class ConsoleException extends Exception
{
    /**
     * Inline exception handler, displays the error message, source of the
     * exception, and the stack trace of the error.
     *
     * Should this display a stack trace? It's useful.
     *
     * Should this still log? Maybe not as useful since we'll see the error on the screen.
     *
     *
     * @param \Throwable $e
     *
     * @return void
     */
    public static function handler(Throwable $e): void
    {
        try {
            echo Exception::oneLiner($e);

            if (class_exists(ORM_Validation_Exception::class) && $e instanceof ORM_Validation_Exception) {
                echo Debug::dump($e->errors('orm'));
            }

            echo PHP_EOL;
            echo $e->getTraceAsString().PHP_EOL;

            $exit_code = $e->getCode();

            // Never exit "0" after an exception.
            if ($exit_code == 0) {
                $exit_code = 1;
            }

            exit($exit_code);
        } catch (Throwable $e) {
            // Clean the output buffer if one exists
            ob_get_level() and ob_clean();

            // Display the exception text
            echo Exception::oneLiner($e), "\n";

            // Exit with an error status
            exit(1);
        }
    }
}
