<?php

namespace BetaKiller\Helper;

use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\Phone;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\EmailMessageTargetInterface;
use BetaKiller\Notification\Message\BroadcastMessageInterface;
use BetaKiller\Notification\Message\DirectMessageInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\PhoneMessageTargetInterface;

interface NotificationGatewayInterface
{
    /**
     * Send direct message to a single user
     *
     * @param \BetaKiller\Notification\MessageTargetInterface         $target
     * @param \BetaKiller\Notification\Message\DirectMessageInterface $message
     *
     * @return void
     */
    public function sendDirect(MessageTargetInterface $target, DirectMessageInterface $message): void;

    /**
     * Send message to a linked group
     *
     * @param \BetaKiller\Notification\Message\BroadcastMessageInterface $message
     *
     * @return void
     */
    public function sendBroadcast(BroadcastMessageInterface $message): void;

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
     * @param string|null $name Full name of recipient
     * @param null|string $lang Target language alpha-2 ISO code
     *
     * @return \BetaKiller\Notification\EmailMessageTargetInterface
     */
    public function emailTarget(string $email, string $name = null, ?string $lang = null): EmailMessageTargetInterface;

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
