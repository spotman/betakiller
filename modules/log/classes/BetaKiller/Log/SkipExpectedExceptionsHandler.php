<?php
declare(strict_types=1);

namespace BetaKiller\Log;

use BetaKiller\ExceptionInterface;
use BetaKiller\Helper\LoggerHelper;
use Monolog\Handler\HandlerWrapper;
use Monolog\LogRecord;

final class SkipExpectedExceptionsHandler extends HandlerWrapper
{
    /**
     * {@inheritdoc}
     */
    public function handle(LogRecord $record): bool
    {
        /** @var \Throwable|null $e */
        $e = $record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION] ?? null;

        // Skip expected exceptions
        if ($e && $e instanceof ExceptionInterface && !$e->isNotificationEnabled()) {
            return false;
        }

        return parent::handle($record);
    }
}
