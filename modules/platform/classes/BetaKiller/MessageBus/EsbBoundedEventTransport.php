<?php

declare(strict_types=1);

namespace BetaKiller\MessageBus;

final class EsbBoundedEventTransport extends AbstractEsbTransport implements BoundedEventTransportInterface
{
    protected function getTopicName(): string
    {
        return 'events.bounded';
    }

    /**
     * @inheritDoc
     */
    public function publishBounded(BoundedEventMessageInterface $event): void
    {
        $this->publishEvent($event);
    }

    /**
     * @inheritDoc
     */
    public function subscribeBounded(string $eventName, callable $handler): void
    {
        $this->subscribeSingle($eventName, $handler);
    }

    public function subscribeAnyBounded(callable $handler): void
    {
        $this->subscribePattern('*', $handler);
    }
}
