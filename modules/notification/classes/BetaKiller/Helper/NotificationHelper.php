<?php

namespace BetaKiller\Helper;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\Phone;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\EmailMessageTarget;
use BetaKiller\Notification\EmailMessageTargetInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\NotificationException;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\PhoneMessageTarget;
use BetaKiller\Notification\PhoneMessageTargetInterface;

final readonly class NotificationHelper implements NotificationGatewayInterface
{
    public const TEST_NOTIFICATIONS_GROUP = 'test-notifications';

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $notification
     * @param \BetaKiller\I18n\I18nFacade                 $i18n
     */
    public function __construct(
        private NotificationFacade $notification,
        private I18nFacade $i18n
    ) {
    }

    private function getMessageGroup(string $messageCodename): NotificationGroupInterface
    {
        return $this->notification->getGroupByMessageCodename($messageCodename);
    }

    /**
     * @inheritDoc
     */
    public function broadcastMessage(string $name, array $templateData = null, array $attachments = null): void
    {
        if (!$this->notification->isBroadcastMessage($name)) {
            throw new NotificationException('Direct message ":name" must not be send via broadcast', [
                ':name' => $name,
            ]);
        }

        $group = $this->getMessageGroup($name);

        $targets = $this->notification->getGroupTargets($group);

        if (!$targets) {
            throw new NotificationException('Missing targets for group ":name"', [
                ':name' => $group->getCodename(),
            ]);
        }

        foreach ($targets as $target) {
            $message = $this->notification->createMessage($name, $target, $templateData, $attachments);

            $this->notification->enqueueImmediate($message);
        }
    }

    /**
     * @inheritDoc
     */
    public function directMessage(
        string $name,
        MessageTargetInterface $target,
        array $templateData = null,
        array $attachments = null
    ): void {
        if ($this->notification->isBroadcastMessage($name)) {
            throw new NotificationException('Broadcast message ":name" can not be send directly', [
                ':name' => $name,
            ]);
        }

        $message = $this->notification->createMessage($name, $target, $templateData, $attachments);

        // Send only if target user allowed this message group
        $this->notification->enqueueImmediate($message);
    }

    /**
     * @inheritDoc
     */
    public function dismissBroadcast(string $name): void
    {
        $this->notification->dismissBroadcast($name);
    }

    /**
     * @inheritDoc
     */
    public function dismissDirect(string $name, MessageTargetInterface $target): void
    {
        $this->notification->dismissDirect($name, $target);
    }

    /**
     * @inheritDoc
     */
    public function emailTarget(string $email, string $name, ?string $lang = null): EmailMessageTargetInterface
    {
        $lang = $lang ?? $this->i18n->getPrimaryLanguage()->getIsoCode();

        return new EmailMessageTarget($email, $name, $lang);
    }

    /**
     * @inheritDoc
     */
    public function phoneTarget(Phone $phone, ?string $lang = null): PhoneMessageTargetInterface
    {
        $lang = $lang ?? $this->i18n->getPrimaryLanguage()->getIsoCode();

        return new PhoneMessageTarget($phone->e164(), $lang);
    }

    /**
     * @inheritDoc
     */
    public function debugEmailTarget(string $name = null): MessageTargetInterface
    {
        $email = getenv('DEBUG_EMAIL_ADDRESS');

        if (!$email) {
            throw new NotificationException('Missing DEBUG_EMAIL_ADDRESS env var');
        }

        return $this->emailTarget(
            $email,
            $name ?? 'Email debugger'
        );
    }

    /**
     * @inheritDoc
     */
    public function getGroupUserConfig(
        NotificationGroupInterface $group,
        UserInterface $user
    ): NotificationGroupUserConfigInterface {
        return $this->notification->getGroupUserConfig($group, $user);
    }
}
