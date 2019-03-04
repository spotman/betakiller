<?php
namespace BetaKiller\Log;

use BetaKiller\ExceptionInterface;

class ExceptionStacktraceProcessor
{
    /**
     * @param  string[][] $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        /** @var \Throwable|null $exception */
        $exception = $record['context'][Logger::CONTEXT_KEY_EXCEPTION] ?? null;

        // Skip expected exceptions
        if ($exception && $exception instanceof ExceptionInterface && !$exception->isNotificationEnabled()) {
            return $record;
        }

        if ($exception) {
            // Find root exception
            while ($exception->getPrevious()) {
                $exception = $exception->getPrevious();
            }

            $record['context']['stacktrace'] = $exception->getTraceAsString();
        }

        return $record;
    }
}
