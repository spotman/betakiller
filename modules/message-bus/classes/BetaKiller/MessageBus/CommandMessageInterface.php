<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface CommandMessageInterface extends MessageInterface
{
    public const SUFFIX = 'Command';
}
