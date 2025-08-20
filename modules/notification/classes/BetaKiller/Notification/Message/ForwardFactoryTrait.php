<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Notification\MessageTargetInterface;

trait ForwardFactoryTrait
{
    public static function getFactoryFor(MessageTargetInterface $target): callable
    {
        return fn() => self::create();
    }
}
