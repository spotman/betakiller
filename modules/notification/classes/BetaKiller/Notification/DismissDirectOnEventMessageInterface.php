<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\MessageBus\EventMessageInterface;

interface DismissDirectOnEventMessageInterface extends EventMessageInterface
{
    public function getDismissibleTarget(): MessageTargetInterface;
}
