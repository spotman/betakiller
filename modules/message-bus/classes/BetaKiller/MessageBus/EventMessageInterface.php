<?php
namespace BetaKiller\MessageBus;


interface EventMessageInterface extends MessageInterface
{
    public const SUFFIX = 'Event';

    /**
     * Must return true if message requires at least one handler to be processed
     *
     * @return bool
     */
    public function handlersRequired(): bool;
}
