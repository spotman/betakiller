<?php
namespace BetaKiller\MessageBus;

interface CommandHandlerInterface extends MessageHandlerInterface
{
    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $message
     *
     * @return mixed|null
     */
    public function handleCommand($message);
}
