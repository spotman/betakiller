<?php

namespace BetaKiller\Log;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class CliProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record)
    {
        global $argv;

        $record['context']['pid'] = \getmypid();
        $record['context']['uid'] = \getmyuid();
        $record['context']['cwd'] = implode(' ', $argv ?: []);

        return $record;
    }
}
