<?php
namespace BetaKiller\Helper;

use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\NotificationMessageFactory;
use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Service\UserService;

class NotificationHelper
{
    /**
     * @var \BetaKiller\Notification\NotificationMessageFactory
     */
    private $messageFactory;

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
     * @param \BetaKiller\Notification\NotificationMessageFactory $factory
     * @param \BetaKiller\Model\UserInterface                     $user
     * @param \BetaKiller\Helper\AppEnv                           $env
     * @param \BetaKiller\Service\UserService                     $userService
     */
    public function __construct(
        NotificationMessageFactory $factory,
        UserInterface $user,
        AppEnv $env,
        UserService $userService
    ) {
        $this->messageFactory = $factory;
        $this->user           = $user;
        $this->appEnv         = $env;
        $this->userService    = $userService;
    }

    /**
     * @param null $name
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function createMessage($name = null): NotificationMessageInterface
    {
        return $this->messageFactory->create($name);
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return NotificationHelper
     */
    public function toDevelopers(NotificationMessageInterface $message): NotificationHelper
    {
        $developers = $this->userService->getDevelopers();

        $message->addTargetUsers($developers);

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return NotificationHelper
     */
    public function toModerators(NotificationMessageInterface $message): NotificationHelper
    {
        $moderators = $this->userService->getModerators();

        $message->addTargetUsers($moderators);

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return NotificationHelper
     * @throws \HTTP_Exception_401
     */
    public function toCurrentUser(NotificationMessageInterface $message): NotificationHelper
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
     */
    public function rewriteTargetsForDebug(NotificationMessageInterface $msg, ?bool $inStage = null): NotificationHelper
    {
        if (!$this->appEnv->inProduction($inStage ?? true)) {
            $msg->clearTargets();

            $this->toDevelopers($msg);
        }

        return $this;
    }
}
