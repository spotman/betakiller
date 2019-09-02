<?php
namespace BetaKiller\MessageBus;

use JsonSerializable;

/**
 * Interface OutboundEventMessageInterface
 * Message requires processing in external message queue (instead of internal one)
 *
 * @package BetaKiller\MessageBus
 */
interface OutboundEventMessageInterface extends EventMessageInterface, JsonSerializable
{
    public function getExternalName(): string;
}
