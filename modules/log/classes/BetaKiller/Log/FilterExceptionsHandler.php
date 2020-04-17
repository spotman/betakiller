<?php
declare(strict_types=1);

namespace BetaKiller\Log;

use BetaKiller\ExceptionInterface;
use BetaKiller\Helper\LoggerHelper;
use Monolog\Handler\HandlerWrapper;

class FilterExceptionsHandler extends HandlerWrapper
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $record): bool
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
