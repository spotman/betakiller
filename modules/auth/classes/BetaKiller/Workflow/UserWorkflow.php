<?php
declare(strict_types=1);

namespace BetaKiller\Workflow;

use BetaKiller\Event\UserBlockedEvent;
use BetaKiller\Event\UserConfirmationEmailRequestedEvent;
use BetaKiller\Event\UserEmailChangedEvent;
use BetaKiller\Event\UserEmailConfirmedEvent;
use BetaKiller\Event\UserResumedEvent;
use BetaKiller\Event\UserSuspendedEvent;
use BetaKiller\Event\UserUnlockedEvent;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\UserInterface;
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
    private $state;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private $eventBus;

    /**
     * UserWorkflow constructor.
     *
     * @param \BetaKiller\Workflow\StatusWorkflowInterface   $workflow
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\MessageBus\EventBusInterface       $eventBus
     */
    public function __construct(
        StatusWorkflowInterface $workflow,
        UserRepositoryInterface $userRepo,
        EventBusInterface $eventBus
    ) {
        $this->state    = $workflow;
        $this->userRepo = $userRepo;
        $this->eventBus = $eventBus;
    }

    public function justCreated(UserInterface $user): void
    {
        $this->state->setStartState($user);
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

    public function block(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_BLOCK, $user);

        $this->userRepo->save($user);

        $this->eventBus->emit(new UserBlockedEvent($user));
    }

    public function unlock(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_UNLOCK, $user);

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
        // Mark user as "claimed" to prevent future communication
        $this->state->doTransition($user, self::TRANSITION_REG_CLAIM, $user);

        $this->userRepo->save($user);
    }
}
