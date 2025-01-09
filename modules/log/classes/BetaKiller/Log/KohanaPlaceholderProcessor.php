<?php

namespace BetaKiller\Log;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class KohanaPlaceholderProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record)
    {
        if ($record['context'] ?? null) {
            $data = [];

            foreach ($record['context'] as $key => $item) {
                if (\is_string($key) && is_scalar($item) && str_starts_with($key, ':')) {
                    $data[$key] = (string)$item;
                    unset($record['context'][$key]);
                }
            }

            $record = $record->with(message: strtr($record['message'], $data));
        }

        return $record;
    }
}
