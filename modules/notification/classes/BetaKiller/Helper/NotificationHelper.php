<?php

namespace BetaKiller\Helper;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\Phone;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\EmailMessageTarget;
use BetaKiller\Notification\EmailMessageTargetInterface;
use BetaKiller\Notification\Envelope;
use BetaKiller\Notification\Message\BroadcastMessageInterface;
use BetaKiller\Notification\Message\DirectMessageInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\NotificationException;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\PhoneMessageTarget;
use BetaKiller\Notification\PhoneMessageTargetInterface;
use BetaKiller\Repository\UserRepositoryInterface;

final readonly class NotificationHelper implements NotificationGatewayInterface
{
    public const TEST_NOTIFICATIONS_GROUP = 'test-notifications';

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade    $notification
     * @param \BetaKiller\I18n\I18nFacade                    $i18n
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     */
    public function __construct(
        private NotificationFacade $notification,
        private I18nFacade $i18n,
        private UserRepositoryInterface $userRepo
    ) {
    }

    /**
     * @inheritDoc
     */
    public function sendDirect(MessageTargetInterface $target, DirectMessageInterface $message): void
    {
        $this->notification->enqueueImmediate(new Envelope($target, $message));
    }

    /**
     * @inheritDoc
     */
    public function sendBroadcast(BroadcastMessageInterface $message): void
    {
        $group = $this->getMessageGroup($message::getCodename());

        $targets = $this->notification->getGroupTargets($group);

        if (!$targets) {
            throw new NotificationException('Missing targets for group ":name"', [
                ':name' => $group->getCodename(),
            ]);
        }

        foreach ($targets as $target) {
            $this->notification->enqueueImmediate(new Envelope($target, $message));
        }
    }

    private function getMessageGroup(string $messageCodename): NotificationGroupInterface
    {
        return $this->notification->getGroupByMessageCodename($messageCodename);
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
    public function emailTarget(string $email, string $name = null, ?string $lang = null): EmailMessageTargetInterface
    {
        $user = $this->userRepo->findByEmail($email);

        if ($user) {
            return $user;
        }

        $lang = $lang ?? $this->i18n->getPrimaryLanguage()->getIsoCode();

        return new EmailMessageTarget($email, $name ?? $email, $lang);
    }

    /**
     * @inheritDoc
     */
    public function phoneTarget(Phone $phone, ?string $lang = null): PhoneMessageTargetInterface
    {
        $user = $this->userRepo->findByPhone($phone);

        if ($user) {
            return $user;
        }

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
