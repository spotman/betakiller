<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Notification\MessageTargetInterface;

final class DeveloperTestBroadcastMessage extends AbstractBroadcastMessage
{
    public static function getCodename(): string
    {
        return 'developer/test/broadcast';
    }

    public static function isCritical(): bool
    {
        return true;
    }

    public static function getFactoryFor(MessageTargetInterface $target): callable
    {
        return fn() => self::create();
    }
}
