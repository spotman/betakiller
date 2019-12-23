<?php
declare(strict_types=1);

namespace BetaKiller\Workflow;

use BetaKiller\Action\Auth\ClaimRegistrationAction;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Service\TokenService;

final class UserWorkflow
{
    public const TRANSITION_EMAIL_CONFIRMED = 'confirm';
    public const TRANSITION_CHANGE_EMAIL    = 'change-email';

    public const TRANSITION_BLOCK     = 'block';
    public const TRANSITION_REG_CLAIM = 'reg-claim';

    public const TRANSITION_SUSPEND            = 'suspend';
    public const TRANSITION_ACTIVATE_SUSPENDED = 'activate';

    public const NOTIFICATION_EMAIL_VERIFICATION = 'auth/verification';

    /**
     * @var \BetaKiller\Workflow\StatusWorkflowInterface
     */
    private $state;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Service\TokenService
     */
    private $tokenService;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * UserWorkflow constructor.
     *
     * @param \BetaKiller\Workflow\StatusWorkflowInterface   $workflow
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Helper\NotificationHelper          $notificationHelper
     * @param \BetaKiller\Service\TokenService               $tokenService
     * @param \BetaKiller\Factory\UrlHelperFactory           $urlHelperFactory
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(
        StatusWorkflowInterface $workflow,
        UserRepositoryInterface $userRepo,
        NotificationHelper $notificationHelper,
        TokenService $tokenService,
        UrlHelperFactory $urlHelperFactory
    ) {
        $this->state        = $workflow;
        $this->userRepo     = $userRepo;
        $this->tokenService = $tokenService;
        $this->notification = $notificationHelper;
        $this->urlHelper    = $urlHelperFactory->create();
    }

    public function justCreated(UserInterface $user): void
    {
        $this->state->setStartState($user);
    }

    public function requestConfirmationEmail(UserInterface $user): void
    {
        $this->sendConfirmationEmail($user);
    }

    public function confirmEmail(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_EMAIL_CONFIRMED, $user);
    }

    public function changeEmail(UserInterface $user, string $email): void
    {
        $user->setEmail($email);

        // Save to keep email on errors
        $this->userRepo->save($user);

        $this->state->doTransition($user, self::TRANSITION_CHANGE_EMAIL, $user);

        // New email => new verification
        $this->sendConfirmationEmail($user);
    }

    public function block(UserInterface $user): void
    {

    }

    public function suspend(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_SUSPEND, $user);
    }

    public function activateSuspended(UserInterface $user): void
    {
        $this->state->doTransition($user, self::TRANSITION_ACTIVATE_SUSPENDED, $user);

        // Send verification link
        $this->sendConfirmationEmail($user);
    }

    public function notRegisteredClaim(UserInterface $user): void
    {
        // Mark user as "claimed" to prevent future communication
        $this->state->doTransition($user, self::TRANSITION_REG_CLAIM, $user);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    private function sendConfirmationEmail(UserInterface $user): void
    {
        $token = $this->tokenService->create($user, new \DateInterval('P14D'));

        $abuseElement = $this->urlHelper->getUrlElementByCodename(ClaimRegistrationAction::codename());

        $emailData = [
            'claim_url' => $this->urlHelper->makeUrl($abuseElement),
            // For action URL generation
            '$token'    => $token,
        ];

        $this->notification->directMessage(self::NOTIFICATION_EMAIL_VERIFICATION, $user, $emailData);
    }
}
