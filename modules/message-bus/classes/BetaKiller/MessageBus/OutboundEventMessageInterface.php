<?php
namespace BetaKiller\MessageBus;

/**
 * Interface OutboundEventMessageInterface
 * Message must be forwarded from the message queue to other sources (another bus, monitoring agent, etc)
 *
 * @package BetaKiller\MessageBus
 */
interface OutboundEventMessageInterface extends ExternalEventMessageInterface
{
    /**
     * @return string
     */
    public function getOutboundName(): string;

    /**
     * @return array|null
     */
    public function getOutboundData(): ?array;
}
