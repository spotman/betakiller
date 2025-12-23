<?php

declare(strict_types=1);

namespace BetaKiller\Workflow;

use BetaKiller\Auth\PasswordHasherInterface;
use BetaKiller\Event\UserApprovedEvent;
use BetaKiller\Event\UserBannedEvent;
use BetaKiller\Event\UserConfirmationEmailRequestedEvent;
use BetaKiller\Event\UserCreatedEvent;
use BetaKiller\Event\UserEmailChangedEvent;
use BetaKiller\Event\UserEmailConfirmedEvent;
use BetaKiller\Event\UserPendingEvent;
use BetaKiller\Event\UserPhoneConfirmedEvent;
use BetaKiller\Event\UserRegistrationClaimedEvent;
use BetaKiller\Event\UserRejectedEvent;
use BetaKiller\Event\UserRemovedEvent;
use BetaKiller\Event\UserResumedEvent;
use BetaKiller\Event\UserSuspendedEvent;
use BetaKiller\Event\UserUnbannedEvent;
use BetaKiller\Exception\DomainException;
use BetaKiller\Factory\UserFactoryInterface;
use BetaKiller\Factory\UserInfo;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\EmailMessageTargetInterface;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;

final readonly class UserWorkflow
{
    public const TRANSITION_CHECK   = 'check';
    public const TRANSITION_APPROVE = 'approve';
    public const TRANSITION_REJECT  = 'reject';
    public const TRANSITION_BAN     = 'ban';
    public const TRANSITION_UNBAN   = 'unban';
    public const TRANSITION_SUSPEND = 'suspend';
    public const TRANSITION_RESUME  = 'resume';
    public const TRANSITION_REMOVE  = 'remove';
    public const TRANSITION_RESTORE = 'restore';

    /**
     * UserWorkflow constructor.
     *
     * @param \BetaKiller\Factory\UserFactoryInterface       $userFactory
     * @param \BetaKiller\Auth\PasswordHasherInterface       $hasher
     * @param \BetaKiller\Workflow\StatusWorkflowInterface   $state
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Repository\RoleRepositoryInterface $roleRepo
     * @param \BetaKiller\MessageBus\EventBusInterface       $eventBus
     */
    public function __construct(
        private UserFactoryInterface $userFactory,
        private PasswordHasherInterface $hasher,
        private StatusWorkflowInterface $state,
        private UserRepositoryInterface $userRepo,
        private RoleRepositoryInterface $roleRepo,
        private EventBusInterface $eventBus
    ) {
    }

    public function create(UserInfo $info): UserInterface
    {
        $primaryRole = $info->role
            ? $this->roleRepo->getByName($info->role)
            : null;

        if ($primaryRole) {
            $loginRole = $this->roleRepo->getLoginRole();

            if (!$this->roleRepo->isInherits($primaryRole, $loginRole)) {
                throw new DomainException('Role ":name" must inherit ":login" role', [
                    ':name'  => $primaryRole->getName(),
                    ':login' => $loginRole->getName(),
                ]);
            }
        }

        $user = $this->userFactory->create($info);

        if ($user::isEmailUniqueEnabled() && $info->email && $this->userRepo->findByEmail($info->email)) {
            throw new DomainException('User ":email" already exists', [
                ':email' => $info->email,
            ]);
        }

        if ($user::isPhoneUniqueEnabled() && $info->phone && $this->userRepo->findByPhone($info->phone)) {
            throw new DomainException('User ":phone" already exists', [
                ':phone' => $info->phone->formatted(),
            ]);
        }

        if ($user::isUsernameUniqueEnabled() && $info->username && $this->userRepo->findByUsername($info->username)) {
            throw new DomainException('User ":email" already exists', [
                ':email' => $info->username,
            ]);
        }

        if ($user::isPasswordEnabled() && $info->password) {
            $hash = $this->hasher->proceed($info->password);
            $user->setPassword($hash);
        }

        // Enable email notifications by default
        if ($user instanceof EmailMessageTargetInterface) {
            $user->enableEmailNotification();
        }

        $this->state->setStartState($user);

        // Create new model via save so ID will be populated for adding roles
        $this->userRepo->save($user);

        if ($primaryRole) {
            $user->addRole($primaryRole);
        }

        $this->eventBus->emit(new UserCreatedEvent($user));

        // Auto-approve (if enabled)
        if ($user::isAutoApproveEnabled()) {
            $this->requestCheck($user);
        }

        return $user;
    }

    public function requestCheck(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_CHECK, $user);

        $this->userRepo->save($user);

        // Auto-approve (if enabled)
        if ($user::isAutoApproveEnabled()) {
            $this->approve($user, $user);
        } else {
            $this->eventBus->emit(new UserPendingEvent($user));
        }
    }

    public function approve(UserInterface $user, UserInterface $byUser): void
    {
        $this->state->doTransition($user, self::TRANSITION_APPROVE, $byUser);

        $user->setApprovedAt();
        $this->userRepo->save($user);

        $this->eventBus->emit(new UserApprovedEvent($user));
    }

    public function reject(UserInterface $user, UserInterface $byUser): void
    {
        $this->state->doTransition($user, self::TRANSITION_REJECT, $byUser);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserRejectedEvent($user));
    }

    public function requestConfirmationEmail(UserInterface $user): void
    {
        $this->eventBus->emit(new UserConfirmationEmailRequestedEvent($user));
    }

    public function confirmEmail(UserInterface $user): void
    {
        if ($user->isEmailVerified()) {
            return;
        }

        $user->markEmailAsVerified();

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserEmailConfirmedEvent($user));
    }

    public function confirmPhone(UserInterface $user): void
    {
        if ($user->isPhoneVerified()) {
            return;
        }

        $user->markPhoneAsVerified();

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserPhoneConfirmedEvent($user));
    }

    public function changeEmail(UserInterface $user, string $email): void
    {
        $user->setEmail($email);
        $user->markEmailAsUnverified();

        // Save to keep email on errors
        $this->userRepo->save($user);

        $this->eventBus->emit(new UserEmailChangedEvent($user));
    }

    public function ban(UserInterface $user, UserInterface $adminUser): void
    {
        $this->state->doTransition($user, self::TRANSITION_BAN, $adminUser);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserBannedEvent($user));
    }

    public function unban(UserInterface $user, UserInterface $adminUser): void
    {
        $this->state->doTransition($user, self::TRANSITION_UNBAN, $adminUser);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserUnbannedEvent($user));
    }

    public function suspend(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_SUSPEND, $user);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserSuspendedEvent($user));
    }

    public function resumeSuspended(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_RESUME, $user);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserResumedEvent($user));

        // Auto-approve (if enabled)
        if ($user::isAutoApproveEnabled()) {
            $this->approve($user, $user);
        }
    }

    public function notRegisteredClaim(UserInterface $user): void
    {
        $user->markAsRegistrationClaimed();

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserRegistrationClaimedEvent($user));
    }

    public function remove(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_REMOVE, $user);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserRemovedEvent($user));
    }

    public function restore(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_RESTORE, $user);

        $this->userRepo->save($user);
    }
}
