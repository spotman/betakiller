<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\LoggerHelper;

class ContextCleanupProcessor
{
    /**
     * @param  string[][] $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        if (isset($record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION])) {
            unset($record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION]);
        }

        if (isset($record['context'][LoggerHelper::CONTEXT_KEY_REQUEST])) {
            unset($record['context'][LoggerHelper::CONTEXT_KEY_REQUEST]);
        }

        if (isset($record['context'][LoggerHelper::CONTEXT_KEY_USER])) {
            unset($record['context'][LoggerHelper::CONTEXT_KEY_USER]);
        }

        return $record;
    }
}
