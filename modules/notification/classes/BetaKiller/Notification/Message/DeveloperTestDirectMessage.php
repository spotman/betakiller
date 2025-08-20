<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Notification\MessageTargetInterface;

final class DeveloperTestDirectMessage extends AbstractDirectMessage
{
    public static function getCodename(): string
    {
        return 'developer/test/direct';
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
