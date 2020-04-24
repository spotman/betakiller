<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface OutboundEventTransportInterface
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
     * Start consuming loop
     *
     * @param \React\EventLoop\LoopInterface $loop
     */
    public function startConsuming(\React\EventLoop\LoopInterface $loop): void;

    /**
     * Stop consuming loop
     *
     * @param \React\EventLoop\LoopInterface $loop
     */
    public function stopConsuming(\React\EventLoop\LoopInterface $loop): void;

}
