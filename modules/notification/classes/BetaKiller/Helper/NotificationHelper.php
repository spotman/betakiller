<?php
namespace BetaKiller\Helper;

use BetaKiller\Repository\UserRepository;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\NotificationMessageFactory;
use BetaKiller\Notification\NotificationMessageInterface;

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
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepository;

    /**
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationMessageFactory $messageFactory
     * @param \BetaKiller\Model\UserInterface                     $user
     * @param \BetaKiller\Helper\AppEnv                           $env
     * @param \BetaKiller\Repository\UserRepository               $userRepository
     */
    public function __construct(NotificationMessageFactory $messageFactory, UserInterface $user, AppEnv $env, UserRepository $userRepository)
    {
        $this->messageFactory = $messageFactory;
        $this->user           = $user;
        $this->appEnv         = $env;
        $this->userRepository = $userRepository;
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
     * @return $this
     */
    public function toDevelopers(NotificationMessageInterface $message)
    {
        $developers = $this->userRepository->getDevelopers();

        $message->addTargetUsers($developers);

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return $this
     */
    public function toModerators(NotificationMessageInterface $message)
    {
        $moderators = $this->userRepository->getModerators();

        $message->addTargetUsers($moderators);

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return $this
     * @throws \HTTP_Exception_401
     */
    public function toCurrentUser(NotificationMessageInterface $message)
    {
        $this->user->forceAuthorization();

        $message->addTarget($this->user);

        return $this;
    }

    public function rewriteTargetsForDebug(NotificationMessageInterface $message, ?bool $keepInStage = null)
    {
        if (!$this->appEnv->inProduction($keepInStage ?? true)) {
            $message->clearTargets();

            $this->toDevelopers($message);
        }

        return $this;
    }
}
