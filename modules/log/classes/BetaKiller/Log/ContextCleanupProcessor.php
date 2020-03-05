<?php
namespace BetaKiller\Log;

class ContextCleanupProcessor
{
    /**
     * @param  string[][] $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        if (isset($record['context'][Logger::CONTEXT_KEY_EXCEPTION])) {
            unset($record['context'][Logger::CONTEXT_KEY_EXCEPTION]);
        }

        if (isset($record['context'][Logger::CONTEXT_KEY_REQUEST])) {
            unset($record['context'][Logger::CONTEXT_KEY_REQUEST]);
        }

        if (isset($record['context'][Logger::CONTEXT_KEY_USER])) {
            unset($record['context'][Logger::CONTEXT_KEY_USER]);
        }

        return $record;
    }
}
