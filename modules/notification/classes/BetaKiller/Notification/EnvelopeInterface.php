<?php

namespace BetaKiller\Notification;

use BetaKiller\Notification\Message\MessageInterface;

/**
 * Interface EnvelopeInterface
 *
 * @package BetaKiller\Notification
 */
interface EnvelopeInterface
{
    /**
     * @return \BetaKiller\Notification\Message\MessageInterface
     */
    public function getMessage(): MessageInterface;

    /**
     * @return \BetaKiller\Notification\MessageTargetInterface
     */
    public function getTarget(): MessageTargetInterface;
}
