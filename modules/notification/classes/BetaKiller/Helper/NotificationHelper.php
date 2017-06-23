<?php
namespace BetaKiller\Helper;

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
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * NotificationHelper constructor.
     *
     * @param \BetaKiller\Notification\NotificationMessageFactory $messageFactory
     * @param \BetaKiller\Model\UserInterface                     $user
     * @param \BetaKiller\Helper\AppEnv                           $env
     */
    public function __construct(NotificationMessageFactory $messageFactory, UserInterface $user, AppEnv $env)
    {
        $this->messageFactory = $messageFactory;
        $this->user           = $user;
        $this->appEnv         = $env;
    }

    /**
     * @param null $name
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function createMessage($name = null)
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
        $developers = $this->user->get_developers_list();

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
        $moderators = $this->user->get_moderators_list();

        $message->addTargetUsers($moderators);

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     *
     * @return $this
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
