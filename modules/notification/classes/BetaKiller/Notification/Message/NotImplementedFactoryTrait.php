<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Notification\MessageTargetInterface;

trait NotImplementedFactoryTrait
{
    final public static function getFactoryFor(MessageTargetInterface $target): callable
    {
        return fn() => throw new NotImplementedHttpException();
    }
}
