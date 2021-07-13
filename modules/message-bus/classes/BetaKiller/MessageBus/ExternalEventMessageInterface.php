<?php
namespace BetaKiller\MessageBus;

/**
 * Interface ExternalEventMessageInterface
 * Message requires processing in external message queue (instead of internal one)
 *
 * @package BetaKiller\MessageBus
 */
interface ExternalEventMessageInterface extends EventMessageInterface
{
    /**
     * Must return string representation of the event name (without binding to FQN)
     *
     * @return string
     */
    public static function getExternalName(): string;
}
