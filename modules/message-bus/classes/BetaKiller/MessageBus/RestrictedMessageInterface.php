<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

/**
 * Interface MessageWithRestrictionsInterface
 * Marks messages which restricted targets
 *
 * @package BetaKiller\MessageBus
 */
interface RestrictedMessageInterface extends MessageInterface
{
    /**
     * @return \BetaKiller\MessageBus\MessageRestrictionInterface
     */
    public function getRestriction(): MessageRestrictionInterface;
}
