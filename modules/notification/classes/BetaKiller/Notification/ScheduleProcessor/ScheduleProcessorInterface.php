<?php
declare(strict_types=1);

namespace BetaKiller\Notification\ScheduleProcessor;

use BetaKiller\Notification\Message\MessageInterface;
use BetaKiller\Notification\MessageTargetInterface;

interface ScheduleProcessorInterface
{
    /**
     * Composes a message and returns false if message is not required
     *
     * @param \BetaKiller\Notification\Message\MessageInterface $message
     * @param \BetaKiller\Notification\MessageTargetInterface   $target
     *
     * @return bool
     */
    public function fillUpMessage(MessageInterface $message, MessageTargetInterface $target): bool;
}
