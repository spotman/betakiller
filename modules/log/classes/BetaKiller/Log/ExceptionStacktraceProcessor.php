<?php

namespace BetaKiller\Log;

use BetaKiller\ExceptionInterface;
use BetaKiller\Helper\LoggerHelper;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class ExceptionStacktraceProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record)
    {
        /** @var \Throwable|null $exception */
        $exception = $record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION] ?? null;

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
