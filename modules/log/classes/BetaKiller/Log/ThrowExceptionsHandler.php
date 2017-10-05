<?php
namespace BetaKiller\Log;

use Monolog\Handler\AbstractHandler;

/**
 * Class ThrowExceptionsHandler
 *
 * @package BetaKiller\Log
 * @deprecated
 */
class ThrowExceptionsHandler extends AbstractHandler
{
    /**
     * ThrowExceptionsHandler constructor.
     * @deprecated
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param  array $record The record to handle
     *
     * @return Boolean true means that this handler handled the record, and that bubbling is not permitted.
     *                        false means the record was either not processed or that this handler allows bubbling.
     */
    public function handle(array $record): bool
    {
        $exception = $record['context']['exception'] ?? null;

        if ($exception && $exception instanceof \Throwable) {
            throw $exception;
        }

        return false;
    }
}
