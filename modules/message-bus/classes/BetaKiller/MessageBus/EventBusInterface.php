<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface EventBusInterface extends AbstractMessageBusInterface
{
    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function emit(EventMessageInterface $message): void;
}
