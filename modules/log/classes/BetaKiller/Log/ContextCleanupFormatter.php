<?php
namespace BetaKiller\Log;

use Monolog\Formatter\LineFormatter;

class ContextCleanupFormatter extends LineFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        if (isset($record['context'][Logger::CONTEXT_KEY_EXCEPTION])) {
            unset($record['context'][Logger::CONTEXT_KEY_EXCEPTION]);
        }

        if (isset($record['context'][Logger::CONTEXT_KEY_REQUEST])) {
            unset($record['context'][Logger::CONTEXT_KEY_REQUEST]);
        }

        return parent::format($record);
    }
}
