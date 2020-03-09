<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface ExternalEventTransportInterface
{
    /**
     * @param \BetaKiller\MessageBus\BoundedEventMessageInterface $message
     */
    public function emitBounded(BoundedEventMessageInterface $message): void;

    /**
     * @param \BetaKiller\MessageBus\OutboundEventMessageInterface $message
     */
    public function emitOutbound(OutboundEventMessageInterface $message): void;
}
