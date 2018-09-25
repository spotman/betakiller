<?php
namespace BetaKiller\Helper;

use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationUserEmail;
use BetaKiller\Notification\NotificationUserInterface;

class NotificationHelper
{
    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private $facade;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $facade
     * @param \BetaKiller\Helper\AppEnvInterface          $appEnv
     * @param \BetaKiller\Model\UserInterface             $currentUser
     */
    public function __construct(
        NotificationFacade $facade,
        UserInterface $currentUser,
        AppEnvInterface $appEnv
    ) {
        $this->facade = $facade;
        $this->appEnv = $appEnv;
        $this->user   = $currentUser;
    }

    /**
     * Send message to a linked group
     *
     * @param string $name
     * @param array  $templateData
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function groupMessage(string $name, array $templateData): void
    {
        $message = $this->facade->groupMessage($name, $templateData);

        $this->send($message);
    }

    /**
     * Send direct message to a single user
     *
     * @param string                                             $name
     * @param \BetaKiller\Notification\NotificationUserInterface $target
     * @param array                                              $templateData
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function directMessage(string $name, NotificationUserInterface $target, array $templateData): void
    {
        $message = $this->facade->directMessage($name, $target, $templateData);

        $this->send($message);
    }

    /**
     * Send message to current user
     *
     * @param string $name
     * @param array  $templateData
     *
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function currentUserMessage(string $name, array $templateData): void
    {
        $this->directMessage($name, $this->currentUserTarget(), $templateData);
    }

    /**
     * Generate target from email
     *
     * @param string      $email
     * @param string      $fullName
     * @param null|string $langName
     *
     * @return \BetaKiller\Notification\NotificationUserInterface
     */
    public function emailTarget(
        string $email,
        string $fullName,
        ?string $langName = null
    ): NotificationUserInterface {
        return new NotificationUserEmail($email, $fullName, $langName);
    }

    /**
     * @return \BetaKiller\Notification\NotificationUserInterface
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     */
    private function currentUserTarget(): NotificationUserInterface
    {
        // Force auth is current user is not logged in
        $this->user->forceAuthorization();

        return $this->user;
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

        return $this->facade->send($message);
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param bool|null                                             $inStage
     *
     * @return void
     */
    private function rewriteTargetsForDebug(NotificationMessageInterface $message, ?bool $inStage = null): void
    {
        if (!$this->appEnv->inProductionMode($inStage ?? true)) {
            $message
                ->clearTargets()
                ->addTarget($this->currentUserTarget());
        }
    }
}
