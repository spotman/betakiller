<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

/**
 * Interface ExternalEventTransportInterface
 *
 * @package BetaKiller\MessageBus
 * @deprecated
 */
interface ExternalEventTransportInterface
{
    /**
     * @param \BetaKiller\MessageBus\ExternalEventMessageInterface $message
     */
    public function publish(ExternalEventMessageInterface $message): void;

    /**
     * @param $externalEvent
     *
     * @return \BetaKiller\MessageBus\BoundedEventMessageInterface
     */
    public function receiveBounded($externalEvent): BoundedEventMessageInterface;

    /**
     * @param $externalEvent
     *
     * @return \BetaKiller\MessageBus\OutboundEventMessageInterface
     */
    public function receiveOutbound($externalEvent): OutboundEventMessageInterface;

    /**
     * @param string   $eventName
     * @param callable $handler
     */
    public function subscribeBounded(string $eventName, callable $handler): void;

    /**
     * Used to listen to all outbound events while forwarding them to another bus
     *
     * @param callable $handler
     */
    public function subscribeAllOutbound(callable $handler): void;

    /**
     * Start consuming loop
     */
    public function startConsuming(): void;

    /**
     * Stop consuming loop
     */
    public function stopConsuming(): void;
}
