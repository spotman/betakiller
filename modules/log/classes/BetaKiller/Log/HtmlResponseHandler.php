<?php
namespace BetaKiller\Log;

use Monolog\Handler\AbstractHandler;

/**
 * Class HtmlResponseHandler
 *
 * @package BetaKiller\Log
 */
class HtmlResponseHandler extends AbstractHandler
{
    /**
     * @var bool
     */
    private $isDebugEnabled;

    /**
     * HtmlResponseHandler constructor.
     */
    public function __construct(bool $isDebugEnabled)
    {
        $this->isDebugEnabled = $isDebugEnabled;

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
            if ($this->isDebugEnabled) {
                $this->sendDebugHtmlResponse($exception);
            } else {
                $this->sendNiceHtmlResponse($exception);
            }
        }

        return true;
    }

    private function sendNiceHtmlResponse(\Throwable $e): void
    {
        // TODO

    }

    private function sendDebugHtmlResponse(\Throwable $e): void
    {
        // TODO

    }
}
