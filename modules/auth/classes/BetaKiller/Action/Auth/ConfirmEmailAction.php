<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Service\TokenService;
use BetaKiller\Workflow\UserWorkflow;

class ConfirmEmailAction extends AbstractTokenVerificationAction
{
    /**
     * @var \BetaKiller\Workflow\UserWorkflow
     */
    private $userWorkflow;

    /**
     * @param \BetaKiller\Service\TokenService          $tokenService
     * @param \BetaKiller\Service\AuthService           $auth
     * @param \BetaKiller\Auth\UserUrlDetectorInterface $urlDetector
     * @param \BetaKiller\Workflow\UserWorkflow         $userWorkflow
     */
    public function __construct(
        TokenService $tokenService,
        AuthService $auth,
        UserUrlDetectorInterface $urlDetector,
        UserWorkflow $userWorkflow
    ) {
        parent::__construct($tokenService, $auth, $urlDetector);

        $this->userWorkflow = $userWorkflow;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return void
     */
    protected function processValid(UserInterface $user): void
    {
        $this->userWorkflow->confirmEmail($user);
    }

    /**
     * @inheritDoc
     */
    protected function isTokenReuseAllowed(): bool
    {
        return true;
    }
}
