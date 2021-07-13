<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface OutboundEventTransportInterface extends EventTransportInterface
{
    /**
     * @param \BetaKiller\MessageBus\OutboundEventMessageInterface $event
     */
    public function publishOutbound(OutboundEventMessageInterface $event): void;

    /**
     * @param callable $handler
     */
    public function subscribeAnyOutbound(callable $handler): void;

    /**
     * @param string   $eventName Outbound name (heartbeat.outbound)
     * @param callable $handler   Function to call on incoming event
     */
    public function subscribeOutbound(string $eventName, callable $handler): void;
}
