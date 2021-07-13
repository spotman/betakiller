<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use React\EventLoop\LoopInterface;

/**
 * Interface EventTransportInterface
 *
 * @package BetaKiller\MessageBus
 */
interface EventTransportInterface
{
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
