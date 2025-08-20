<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

trait CriticalMessageTrait
{
    public static function isCritical(): bool
    {
        return true;
    }
}
