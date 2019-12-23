<?php
namespace BetaKiller\Helper;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\MessageTargetEmail;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\NotificationFacade;

class NotificationHelper
{
    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private $notification;

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $facade
     * @param \BetaKiller\I18n\I18nFacade                 $i18n
     */
    public function __construct(
        NotificationFacade $facade,
        I18nFacade $i18n
    ) {
        $this->notification = $facade;
        $this->i18n         = $i18n;
    }

    public function getMessageGroup(string $messageCodename): NotificationGroupInterface
    {
        return $this->notification->getGroupByMessageCodename($messageCodename);
    }

    /**
     * Send message to a linked group
     *
     * @param string        $name
     * @param array         $templateData
     *
     * @param string[]|null $attachments
     *
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function groupMessage(string $name, array $templateData, array $attachments = null): void
    {
        $group = $this->getMessageGroup($name);

        foreach ($this->notification->getGroupTargets($group) as $target) {
            $this->directMessage($name, $target, $templateData, $attachments);
        }
    }

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
    ): void {
        $message = $this->notification->createMessage($name, $target, $templateData, $attachments);

        // Send only if target user allowed this message group
        $this->notification->enqueue($message);
    }

    /**
     * Generate target from email
     *
     * @param string      $email
     * @param string      $name Full name of recipient
     * @param null|string $lang Target language alpha-2 ISO code
     *
     * @return \BetaKiller\Notification\MessageTargetInterface
     */
    public function emailTarget(string $email, string $name, ?string $lang = null): MessageTargetInterface
    {
        $lang = $lang ?? $this->i18n->getPrimaryLanguage()->getIsoCode();

        return new MessageTargetEmail($email, $name, $lang);
    }

    public function getGroupUserConfig(
        NotificationGroupInterface $group,
        UserInterface $user
    ): NotificationGroupUserConfigInterface {
        return $this->notification->getGroupUserConfig($group, $user);
    }
}
