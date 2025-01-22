<?php

namespace BetaKiller\Log;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class CliProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record)
    {
        global $argv;

        return $record->with(
            context: array_merge($record->context, [
                'pid' => \getmypid(),
                'uid' => \getmyuid(),
                'cwd' => implode(' ', $argv ?: []),
            ])
        );
    }
}
