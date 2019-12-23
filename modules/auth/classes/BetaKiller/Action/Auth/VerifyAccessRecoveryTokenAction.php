<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Auth\PasswordChangeIFace;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Service\TokenService;
use BetaKiller\Workflow\UserWorkflow;

class VerifyAccessRecoveryTokenAction extends AbstractTokenVerificationAction
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
        // Confirm user email if not verified (no errors on duplicate token requests)
        if (!$user->isEmailConfirmed()) {
            $this->userWorkflow->confirmEmail($user);
        }
    }

    /**
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string
     */
    protected function getSuccessUrl(UrlHelper $urlHelper, UserInterface $user): string
    {
        // Password change is required after successful access recovery
        $element = $urlHelper->getUrlElementByCodename(PasswordChangeIFace::codename());

        // Bind user language
        $params = $urlHelper->createUrlContainer()
            ->setEntity($user);

        return $urlHelper->makeUrl($element, $params);
    }
}
