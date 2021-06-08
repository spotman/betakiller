<?php
declare(strict_types=1);

namespace BetaKiller\Api\Method\UserNotification;

use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Repository\NotificationGroupRepository;
use Spotman\Api\ApiAccessViolationException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

abstract class AbstractUpdateGroupApiMethod extends AbstractApiMethod
{
    private const ARG_CODENAME = 'codename';

    /**
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private NotificationGroupRepository $repo;

    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private NotificationFacade $notification;

    /**
     * AbstractUpdateGroupApiMethod constructor.
     *
     * @param \BetaKiller\Repository\NotificationGroupRepository $repo
     * @param \BetaKiller\Notification\NotificationFacade        $notification
     */
    public function __construct(NotificationGroupRepository $repo, NotificationFacade $notification)
    {
        $this->repo = $repo;
        $this->notification = $notification;
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_CODENAME);
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $codename = $arguments->getString(self::ARG_CODENAME);

        $group = $this->repo->getByCodename($codename);

        if (!$this->notification->isGroupAllowedToUser($group, $user)) {
            throw new ApiAccessViolationException('User ":user" is trying to enable notification group ":group"', [
                ':user'  => $user->getID(),
                ':group' => $group->getCodename(),
            ]);
        }

        $this->processGroup($group, $user);

        $this->repo->save($group);

        return null;
    }

    abstract protected function processGroup(NotificationGroupInterface $group, UserInterface $user): void;
}
