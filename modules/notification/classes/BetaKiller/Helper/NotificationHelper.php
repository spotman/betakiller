<?php
namespace BetaKiller\Helper;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationUserEmail;
use BetaKiller\Service\UserService;

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
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * @var \BetaKiller\Config\NotificationConfigInterface
     */
    private $notificationConfig;

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade    $facade
     * @param \BetaKiller\Model\UserInterface                $user
     * @param \BetaKiller\Helper\AppEnvInterface             $env
     * @param \BetaKiller\Service\UserService                $userService
     * @param \BetaKiller\Config\NotificationConfigInterface $notificationConfig
     */
    public function __construct(
        NotificationFacade $facade,
        UserInterface $user,
        AppEnvInterface $env,
        UserService $userService,
        NotificationConfigInterface $notificationConfig
    ) {
        $this->facade             = $facade;
        $this->user               = $user;
        $this->appEnv             = $env;
        $this->userService        = $userService;
        $this->notificationConfig = $notificationConfig;
    }

    /**
     * @param string $messageCodename
     *
     * @return string
     */
    public function getGroupCodename(string $messageCodename): string
    {
        $groupCodename = $this->notificationConfig->getMessageGroup($messageCodename);
        var_dump($groupCodename);
        exit;
    }

    /**
     * @param null $name
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function createMessage($name = null): NotificationMessageInterface
    {
        return $this->facade->create($name);
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return int
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function send(NotificationMessageInterface $message): int
    {
        return $this->facade->send($message);
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return NotificationHelper
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function toDevelopers(NotificationMessageInterface $message): self
    {
        $developers = $this->userService->getDevelopers();

        $message->addTargetUsers($developers);

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return NotificationHelper
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function toModerators(NotificationMessageInterface $message): self
    {
        $moderators = $this->userService->getModerators();

        $message->addTargetUsers($moderators);

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return NotificationHelper
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     */
    public function toCurrentUser(NotificationMessageInterface $message): self
    {
        $this->user->forceAuthorization();

        $message->addTarget($this->user);

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param string                                                $email
     * @param string                                                $fullName
     * @param null|string                                           $langName
     *
     * @return \BetaKiller\Helper\NotificationHelper
     */
    public function toEmail(
        NotificationMessageInterface $message,
        string $email,
        string $fullName,
        ?string $langName = null
    ): self {
        $message->addTarget(new NotificationUserEmail($email, $fullName, $langName));

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $msg
     * @param bool|null                                             $inStage
     *
     * @return \BetaKiller\Helper\NotificationHelper
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function rewriteTargetsForDebug(NotificationMessageInterface $msg, ?bool $inStage = null): self
    {
        if (!$this->appEnv->inProductionMode($inStage ?? true)) {
            $msg->clearTargets();

            $this->toDevelopers($msg);
        }

        return $this;
    }
}
