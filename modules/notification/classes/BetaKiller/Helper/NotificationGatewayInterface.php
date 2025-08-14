<?php

namespace BetaKiller\Helper;

use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\Phone;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\EmailMessageTargetInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\PhoneMessageTargetInterface;

interface NotificationGatewayInterface
{
    /**
     * Send message to a linked group
     *
     * @param string        $name
     * @param array|null    $templateData
     *
     * @param string[]|null $attachments
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function broadcastMessage(string $name, array $templateData = null, array $attachments = null): void;

    /**
     * Send direct message to a single user
     *
     * @param string                                          $name
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param array|null                                      $templateData
     * @param string[]                                        $attachments Array of files to attach
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function directMessage(
        string $name,
        MessageTargetInterface $target,
        array $templateData = null,
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
     * @return \BetaKiller\Notification\EmailMessageTargetInterface
     */
    public function emailTarget(string $email, string $name, ?string $lang = null): EmailMessageTargetInterface;

    /**
     * Generate target from phone number
     *
     * @param \BetaKiller\Model\Phone $phone
     * @param null|string             $lang Target language alpha-2 ISO code
     *
     * @return \BetaKiller\Notification\PhoneMessageTargetInterface
     */
    public function phoneTarget(Phone $phone, ?string $lang = null): PhoneMessageTargetInterface;

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
