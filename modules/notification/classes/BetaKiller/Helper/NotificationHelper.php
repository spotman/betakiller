<?php
namespace BetaKiller\Helper;

use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\NotificationMessageInterface;
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
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $facade
     * @param \BetaKiller\Model\UserInterface             $user
     * @param \BetaKiller\Helper\AppEnv                   $env
     * @param \BetaKiller\Service\UserService             $userService
     */
    public function __construct(
        NotificationFacade $facade,
        UserInterface $user,
        AppEnv $env,
        UserService $userService
    ) {
        $this->facade      = $facade;
        $this->user        = $user;
        $this->appEnv      = $env;
        $this->userService = $userService;
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
