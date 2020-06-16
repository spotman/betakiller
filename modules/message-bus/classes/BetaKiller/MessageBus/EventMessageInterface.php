<?php
namespace BetaKiller\MessageBus;


interface EventMessageInterface extends MessageInterface
{
    public const SUFFIX = 'Event';
}
