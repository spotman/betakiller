<?php
namespace BetaKiller\MessageBus;


interface MessageInterface
{
    /**
     * Must return true if message requires at least one handler to be processed
     *
     * @return bool
     */
    public function handlersRequired(): bool;
}
