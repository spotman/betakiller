<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Service\TokenService;
use BetaKiller\Service\UserVerificationService;

class ConfirmEmailAction extends AbstractTokenVerificationAction
{
    /**
     * @var \BetaKiller\Service\UserVerificationService
     */
    private $emailService;

    /**
     * @param \BetaKiller\Service\TokenService            $tokenService
     * @param \BetaKiller\Service\AuthService             $auth
     * @param \BetaKiller\Auth\UserUrlDetectorInterface   $urlDetector
     * @param \BetaKiller\Service\UserVerificationService $emailService
     */
    public function __construct(
        TokenService $tokenService,
        AuthService $auth,
        UserUrlDetectorInterface $urlDetector,
        UserVerificationService $emailService
    ) {
        parent::__construct($tokenService, $auth, $urlDetector);

        $this->emailService = $emailService;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return void
     */
    protected function processValid(UserInterface $user): void
    {
        $this->emailService->confirmUser($user);
    }
}
