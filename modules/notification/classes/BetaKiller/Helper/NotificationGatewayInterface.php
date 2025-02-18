<?php

namespace BetaKiller\Helper;

use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\MessageTargetInterface;

interface NotificationGatewayInterface
{
    /**
     * Send message to a linked group
     *
     * @param string        $name
     * @param array         $templateData
     *
     * @param string[]|null $attachments
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function broadcastMessage(string $name, array $templateData, array $attachments = null): void;

    /**
     * Send direct message to a single user
     *
     * @param string                                          $name
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param array                                           $templateData
     * @param string[]                                        $attachments Array of files to attach
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function directMessage(
        string $name,
        MessageTargetInterface $target,
        array $templateData,
        array $attachments = null
    ): void;

    /**
     * @param string $name
     *
     * @return void
     */
    public function dismissBroadcast(string $name): void;

    /**
     * @param string                                          $name
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     *
     * @return void
     */
    public function dismissDirect(string $name, MessageTargetInterface $target): void;

    /**
     * Generate target from email
     *
     * @param string      $email
     * @param string      $name Full name of recipient
     * @param null|string $lang Target language alpha-2 ISO code
     *
     * @return \BetaKiller\Notification\MessageTargetInterface
     */
    public function emailTarget(string $email, string $name, ?string $lang = null): MessageTargetInterface;

    /**
     * @param string|null $name
     *
     * @return \BetaKiller\Notification\MessageTargetInterface
     */
    public function debugEmailTarget(string $name = null): MessageTargetInterface;

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     * @param \BetaKiller\Model\UserInterface              $user
     *
     * @return \BetaKiller\Model\NotificationGroupUserConfigInterface
     */
    public function getGroupUserConfig(
        NotificationGroupInterface $group,
        UserInterface $user
    ): NotificationGroupUserConfigInterface;
}
