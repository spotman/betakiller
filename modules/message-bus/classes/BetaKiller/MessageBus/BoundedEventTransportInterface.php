<?php

declare(strict_types=1);

namespace BetaKiller\MessageBus;

/**
 * Interface BoundedEventTransportInterface
 *
 * @package BetaKiller\MessageBus
 */
interface BoundedEventTransportInterface extends EventTransportInterface
{
    /**
     * @param \BetaKiller\MessageBus\BoundedEventMessageInterface $event
     */
    public function publishBounded(BoundedEventMessageInterface $event): void;

    /**
     * @param string   $eventName
     * @param callable $handler
     */
    public function subscribeBounded(string $eventName, callable $handler): void;

    /**
     * @param callable $handler
     *
     * @return void
     */
    public function subscribeAnyBounded(callable $handler): void;
}
