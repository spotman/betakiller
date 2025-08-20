<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

trait NonCriticalMessageTrait
{
    final public static function isCritical(): bool
    {
        return false;
    }
}
