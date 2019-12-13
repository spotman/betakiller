<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

interface MessageActionUrlGeneratorInterface
{
    /**
     * @param string                                          $actionName
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param array                                           $data
     *
     * @return string
     */
    public function make(string $actionName, MessageTargetInterface $target, array $data): string;
}
