<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Auth\PasswordChangeIFace;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Service\TokenService;
use BetaKiller\Workflow\UserWorkflow;

readonly class VerifyAccessRecoveryTokenAction extends AbstractTokenVerificationAction
{
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
        private UserWorkflow $userWorkflow
    ) {
        parent::__construct($tokenService, $auth, $urlDetector);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return void
     */
    protected function processValid(UserInterface $user): void
    {
        // Confirm user email if not verified (no errors on duplicate token requests)
        $this->userWorkflow->confirmEmail($user);
    }

    /**
     * @param \BetaKiller\Helper\UrlHelperInterface $urlHelper
     * @param \BetaKiller\Model\UserInterface       $user
     *
     * @return string
     */
    protected function getSuccessUrl(UrlHelperInterface $urlHelper, UserInterface $user): string
    {
        // Password change is required after successful access recovery
        // Bind user language
        $params = $urlHelper->createUrlContainer()
            ->setEntity($user);

        return $urlHelper->makeCodenameUrl(PasswordChangeIFace::codename(), $params);
    }

    /**
     * @inheritDoc
     */
    protected function isTokenReuseAllowed(): bool
    {
        return false;
    }
}
