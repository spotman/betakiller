<?php
namespace BetaKiller\Notification;

class MessageFactory
{
    /**
     * @param string                                          $codename
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param string                                          $transport
     * @param bool                                            $isCritical
     *
     * @return \BetaKiller\Notification\MessageInterface
     */
    public function create(
        string $codename,
        MessageTargetInterface $target,
        string $transport,
        bool $isCritical
    ): MessageInterface {
        return new Message($codename, $target, $transport, $isCritical);
    }
}
