<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\MessageBus\BoundedEventMessageInterface;

/**
 * Interface DismissDirectOnEventMessageInterface
 * These events are going through ESB and are subject to dismiss notification
 *
 * @package BetaKiller\Notification
 */
interface DismissDirectOnEventMessageInterface extends BoundedEventMessageInterface
{
    public function getDismissibleTarget(): MessageTargetInterface;
}
