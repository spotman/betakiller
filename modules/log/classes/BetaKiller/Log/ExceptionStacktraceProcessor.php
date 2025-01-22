<?php

namespace BetaKiller\Log;

use BetaKiller\ExceptionInterface;
use BetaKiller\Helper\LoggerHelper;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use phpDocumentor\Reflection\Types\Context;

class ExceptionStacktraceProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record)
    {
        /** @var \Throwable|null $exception */
        $exception = $record->context[LoggerHelper::CONTEXT_KEY_EXCEPTION] ?? null;

        // Skip expected exceptions
        if ($exception instanceof ExceptionInterface && !$exception->isNotificationEnabled()) {
            return $record;
        }

        if (!$exception) {
            return $record;
        }

        // Find root exception
        while ($exception->getPrevious()) {
            $exception = $exception->getPrevious();
        }

        return $record->with(
            context: array_merge($record->context, [
                'stacktrace' => $exception->getTraceAsString(),
            ])
        );
    }
}
