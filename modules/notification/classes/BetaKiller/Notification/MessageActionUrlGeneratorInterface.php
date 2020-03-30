<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

interface MessageActionUrlGeneratorInterface
{
    /**
     * Returns null if no action defined for provided message
     *
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @return string|null
     */
    public function make(MessageInterface $message): ?string;
}
