<?php
namespace BetaKiller\Log;

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
