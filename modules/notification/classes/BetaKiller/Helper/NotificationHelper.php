<?php
namespace BetaKiller\Helper;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\MessageInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\TargetEmail;
use BetaKiller\Notification\TargetInterface;
use BetaKiller\Repository\UserRepository;

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
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $facade
     * @param \BetaKiller\Helper\AppEnvInterface          $appEnv
     * @param \BetaKiller\I18n\I18nFacade                 $i18n
     * @param \BetaKiller\Repository\UserRepository       $userRepo
     */
    public function __construct(
        NotificationFacade $facade,
        AppEnvInterface $appEnv,
        I18nFacade $i18n,
        UserRepository $userRepo
    ) {
        $this->notification = $facade;
        $this->appEnv       = $appEnv;
        $this->i18n         = $i18n;
        $this->userRepo     = $userRepo;
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
     * @param string                                   $name
     * @param \BetaKiller\Notification\TargetInterface $target
     * @param array                                    $templateData
     * @param string[]                                 $attachments Array of files to attach
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function directMessage(
        string $name,
        TargetInterface $target,
        array $templateData,
        array $attachments = null
    ): void {
        $message = $this->notification->createMessage($name, $target, $templateData, $attachments);

        // Send only if target user allowed this message group
        $this->enqueue($message);
    }

    /**
     * Generate target from email
     *
     * @param string      $email
     * @param string      $name Full name of recipient
     * @param null|string $lang Target language alpha-2 ISO code
     *
     * @return \BetaKiller\Notification\TargetInterface
     */
    public function emailTarget(string $email, string $name, ?string $lang = null): TargetInterface
    {
        $lang = $lang ?? $this->i18n->getDefaultLanguage()->getIsoCode();

        return new TargetEmail($email, $name, $lang);
    }

    public function getGroupUserConfig(NotificationGroupInterface $group, UserInterface $user): NotificationGroupUserConfigInterface
    {
        return $this->notification->getGroupUserConfig($group, $user);
    }

    /**
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @return void
     * @throws \BetaKiller\Notification\NotificationException
     */
    private function enqueue(MessageInterface $message): void
    {
        $this->rewriteTargetForDebug($message);

        $this->notification->enqueue($message);
    }

    /**
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @return void
     */
    private function rewriteTargetForDebug(MessageInterface $message): void
    {
        if (!$this->appEnv->isDebugEnabled()) {
            return;
        }

        $email  = $this->appEnv->getDebugEmail();
        $target = $this->userRepo->searchBy($email);

        if (!$target) {
            $target = $this->emailTarget($email, 'Debug email target', LanguageInterface::ISO_EN);
        }

        $message->setTarget($target);
    }
}
