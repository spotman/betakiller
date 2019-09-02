<?php
namespace BetaKiller\MessageBus;

/**
 * Interface ExternalCommandMessageInterface
 * Message requires processing in external message queue (instead of internal one)
 *
 * @package BetaKiller\MessageBus
 */
interface ExternalCommandMessageInterface extends CommandMessageInterface
{
    public static function getRpcName(): string;
}
