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
}
