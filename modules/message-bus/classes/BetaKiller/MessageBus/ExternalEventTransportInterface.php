<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface ExternalEventTransportInterface
{
    /**
     * @param \BetaKiller\MessageBus\OutboundEventMessageInterface $message
     *
     * @return void
     */
    public function emit(OutboundEventMessageInterface $message): void;
}
