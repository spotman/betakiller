<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface CommandBusInterface extends AbstractMessageBusInterface
{
    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $message
     *
     * @return void
     */
    public function enqueue(CommandMessageInterface $message): void;

    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $command
     *
     * @return bool
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function handle(CommandMessageInterface $command): bool;
}
