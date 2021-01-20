<?php
declare(strict_types=1);

namespace BetaKiller\Workflow;

use BetaKiller\Event\UserBlockedEvent;
use BetaKiller\Event\UserConfirmationEmailRequestedEvent;
use BetaKiller\Event\UserCreatedEvent;
use BetaKiller\Event\UserEmailChangedEvent;
use BetaKiller\Event\UserEmailConfirmedEvent;
use BetaKiller\Event\UserRegistrationClaimedEvent;
use BetaKiller\Event\UserResumedEvent;
use BetaKiller\Event\UserSuspendedEvent;
use BetaKiller\Event\UserUnlockedEvent;
use BetaKiller\Exception\DomainException;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;

final class UserWorkflow
{
    public const TRANSITION_EMAIL_CONFIRMED = 'confirm';
    public const TRANSITION_CHANGE_EMAIL    = 'change-email';

    public const TRANSITION_BLOCK     = 'block';
    public const TRANSITION_UNLOCK    = 'unlock';
    public const TRANSITION_REG_CLAIM = 'reg-claim';

    public const TRANSITION_SUSPEND          = 'suspend';
    public const TRANSITION_RESUME_SUSPENDED = 'resume';

    /**
     * @var \BetaKiller\Workflow\StatusWorkflowInterface
     */
    private StatusWorkflowInterface $state;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepo;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private EventBusInterface $eventBus;

    /**
     * @var \BetaKiller\Repository\RoleRepositoryInterface
     */
    private RoleRepositoryInterface $roleRepo;

    /**
     * @var \BetaKiller\Factory\EntityFactoryInterface
     */
    private EntityFactoryInterface $entityFactory;

    /**
     * UserWorkflow constructor.
     *
     * @param \BetaKiller\Workflow\StatusWorkflowInterface   $workflow
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Repository\RoleRepositoryInterface $roleRepo
     * @param \BetaKiller\Factory\EntityFactoryInterface     $entityFactory
     * @param \BetaKiller\MessageBus\EventBusInterface       $eventBus
     */
    public function __construct(
        StatusWorkflowInterface $workflow,
        UserRepositoryInterface $userRepo,
        RoleRepositoryInterface $roleRepo,
        EntityFactoryInterface $entityFactory,
        EventBusInterface $eventBus
    ) {
        $this->state         = $workflow;
        $this->userRepo      = $userRepo;
        $this->roleRepo      = $roleRepo;
        $this->entityFactory = $entityFactory;

        $this->eventBus = $eventBus;
    }

    public function create(
        string $email,
        string $primaryRoleName,
        string $createdFromIp,
        string $username = null,
        callable $callback = null
    ): UserInterface {
        if ($this->userRepo->searchBy($email)) {
            throw new DomainException('User ":email" already exists', [
                ':email' => $email,
            ]);
        }

        $primaryRole = $this->roleRepo->getByName($primaryRoleName);
        $loginRole   = $this->roleRepo->getLoginRole();

        if (!$primaryRole->isInherits($loginRole)) {
            throw new DomainException('Role ":name" must inherit ":login" role', [
                ':name'  => $primaryRole->getName(),
                ':login' => $loginRole->getName(),
            ]);
        }

        /** @var UserInterface $user */
        $user = $this->entityFactory->create(User::getModelName());

        $user
            ->setCreatedAt()
            ->setEmail($email)
            ->setCreatedFromIP($createdFromIp);

        if ($username) {
            $user->setUsername($username);
        }

        // Enable email notifications by default
        $user->enableEmailNotification();

        $this->state->setStartState($user);

        // Create new model via save so ID will be populated for adding roles
        $this->userRepo->save($user);

        $user->addRole($primaryRole);

        // Call custom callback and save User
        if ($callback) {
            $callback($user);
            $this->userRepo->save($user);
        }

        $this->eventBus->emit(new UserCreatedEvent($user));

        return $user;
    }

    public function requestConfirmationEmail(UserInterface $user): void
    {
        $this->eventBus->emit(new UserConfirmationEmailRequestedEvent($user));
    }

    public function confirmEmail(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_EMAIL_CONFIRMED, $user);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserEmailConfirmedEvent($user));
    }

    public function changeEmail(UserInterface $user, string $email): void
    {
        $user->setEmail($email);

        // Save to keep email on errors
        $this->userRepo->save($user);

        $this->state->doTransition($user, self::TRANSITION_CHANGE_EMAIL, $user);

        $this->eventBus->emit(new UserEmailChangedEvent($user));
    }

    public function block(UserInterface $user, UserInterface $adminUser): void
    {
        $this->state->doTransition($user, self::TRANSITION_BLOCK, $adminUser);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserBlockedEvent($user));
    }

    public function unlock(UserInterface $user, UserInterface $adminUser): void
    {
        $this->state->doTransition($user, self::TRANSITION_UNLOCK, $adminUser);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserUnlockedEvent($user));
    }

    public function suspend(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_SUSPEND, $user);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserSuspendedEvent($user));
    }

    public function resumeSuspended(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_RESUME_SUSPENDED, $user);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserResumedEvent($user));
    }

    public function notRegisteredClaim(UserInterface $user): void
    {
        $user->markAsRegistrationClaimed();

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserRegistrationClaimedEvent($user));
    }
}
