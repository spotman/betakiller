<?php
declare(strict_types=1);

namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\TransportInterface;

/**
 * Interface DismissibleTransportInterface
 * Defines transport which messages can be dismissed after sending
 *
 * @package BetaKiller\Notification\Transport
 */
interface DismissibleTransportInterface extends TransportInterface
{
    /**
     * @param string                                          $messageCodename
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     *
     * @return void
     */
    public function dismissFor(string $messageCodename, MessageTargetInterface $target): void;
}
