<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use React\EventLoop\LoopInterface;

/**
 * Interface BoundedEventTransportInterface
 *
 * @package BetaKiller\MessageBus
 */
interface BoundedEventTransportInterface
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
     * Start consuming loop
     *
     * @param \React\EventLoop\LoopInterface $loop
     */
    public function startConsuming(LoopInterface $loop): void;

    /**
     * Stop consuming loop
     *
     * @param \React\EventLoop\LoopInterface $loop
     */
    public function stopConsuming(LoopInterface $loop): void;
}
