<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

final class EsbOutboundEventTransport extends AbstractEsbTransport implements OutboundEventTransportInterface
{
    protected function getTopicName(): string
    {
        return 'events.outbound';
    }

    /**
     * @inheritDoc
     */
    public function publishOutbound(OutboundEventMessageInterface $event): void
    {
        $this->publishEvent($event);
    }

    /**
     * @inheritDoc
     */
    public function subscribeAnyOutbound(callable $handler): void
    {
        $this->subscribePattern('*', $handler);
    }

    /**
     * @inheritDoc
     */
    public function subscribeOutbound(string $eventName, callable $handler): void
    {
        $this->subscribeSingle($eventName, $handler);
    }
}
