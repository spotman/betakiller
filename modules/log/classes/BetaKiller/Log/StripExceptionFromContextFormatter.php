<?php
namespace BetaKiller\Log;

use Monolog\Formatter\LineFormatter;

class StripExceptionFromContextFormatter extends LineFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        if (isset($record['context']['exception'])) {
            unset($record['context']['exception']);
        }

        return parent::format($record);
    }
}
