<?php
namespace BetaKiller\Helper;

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
    private $facade;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $facade
     * @param \BetaKiller\Helper\AppEnvInterface          $appEnv
     */
    public function __construct(NotificationFacade $facade, AppEnvInterface $appEnv)
    {
        $this->facade = $facade;
        $this->appEnv = $appEnv;
    }

    public function getMessageGroup(string $messageCodename): NotificationGroupInterface
    {
        return $this->facade->getGroupByMessageCodename($messageCodename);
    }

    /**
     * Send message to a linked group
     *
     * @param string     $name
     * @param array      $templateData
     *
     * @param string[]|null $attachments
     *
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function groupMessage(string $name, array $templateData, array $attachments = null): void
    {
        $message = $this->facade->groupMessage($name, $templateData);

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
     * @param string[] $attachments Array of files to attach
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
        $message = $this->facade->directMessage($name, $target, $templateData);

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
            ? $this->facade->send($message)
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
                ->addTarget($this->emailTarget($debugEmail, 'Debug email target'));
        }
    }
}
