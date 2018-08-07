<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface CommandBusInterface extends AbstractMessageBusInterface
{
    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @return mixed|null
     */
    public function run(CommandMessageInterface $message);
}
