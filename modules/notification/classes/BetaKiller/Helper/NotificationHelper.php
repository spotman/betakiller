<?php
namespace BetaKiller\Helper;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationTargetEmail;
use BetaKiller\Notification\NotificationTargetInterface;

class NotificationHelper
{
    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private $notification;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $facade
     * @param \BetaKiller\Helper\AppEnvInterface          $appEnv
     * @param \BetaKiller\I18n\I18nFacade                 $i18n
     */
    public function __construct(NotificationFacade $facade, AppEnvInterface $appEnv, I18nFacade $i18n)
    {
        $this->notification = $facade;
        $this->appEnv       = $appEnv;
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
        $message = $this->notification->groupMessage($name, $templateData);

        if ($attachments) {
            foreach ($attachments as $attach) {
                $message->addAttachment($attach);
            }
        }

        // Send only if there are targets (maybe all users disabled this group)
        $this->send($message);
    }

    /**
     * Send direct message to a single user
     *
     * @param string                                               $name
     * @param \BetaKiller\Notification\NotificationTargetInterface $target
     * @param array                                                $templateData
     *
     * @param string[]                                             $attachments Array of files to attach
     *
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function directMessage(
        string $name,
        NotificationTargetInterface $target,
        array $templateData,
        array $attachments = null
    ): void {
        $message = $this->notification->directMessage($name, $target, $templateData);

        if ($attachments) {
            foreach ($attachments as $attach) {
                $message->addAttachment($attach);
            }
        }

        // Send only if target user allowed this message group
        $this->send($message);
    }

    /**
     * Generate target from email
     *
     * @param string      $email
     * @param string      $fullName
     * @param null|string $langName
     *
     * @return \BetaKiller\Notification\NotificationTargetInterface
     */
    public function emailTarget(
        string $email,
        string $fullName,
        ?string $langName = null
    ): NotificationTargetInterface {
        $langName = $langName ?? $this->i18n->getDefaultLanguageName();

        return new NotificationTargetEmail($email, $fullName, $langName);
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return int
     * @throws \BetaKiller\Notification\NotificationException
     */
    private function send(NotificationMessageInterface $message): int
    {
        $this->rewriteTargetsForDebug($message);

        // Send only if targets were specified or message group was allowed
        return $message->getTargets()
            ? $this->notification->send($message)
            : 0;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return void
     */
    private function rewriteTargetsForDebug(NotificationMessageInterface $message): void
    {
        if (!$this->appEnv->inProductionMode()) {
            $debugEmail = $this->appEnv->getDebugEmail();

            $message
                ->clearTargets()
                ->addTarget($this->emailTarget(
                    $debugEmail,
                    'Debug email target',
                    LanguageInterface::NAME_EN
                ));
        }
    }
}
