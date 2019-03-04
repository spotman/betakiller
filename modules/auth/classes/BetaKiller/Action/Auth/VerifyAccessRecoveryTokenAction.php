<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Auth\PasswordChangeIFace;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Service\TokenService;
use BetaKiller\Service\UserVerificationService;

class VerifyAccessRecoveryTokenAction extends AbstractTokenVerificationAction
{
    /**
     * @var \BetaKiller\Service\UserVerificationService
     */
    private $verification;

    /**
     * @param \BetaKiller\Service\TokenService            $tokenService
     * @param \BetaKiller\Service\AuthService             $auth
     * @param \BetaKiller\Auth\UserUrlDetectorInterface   $urlDetector
     * @param \BetaKiller\Service\UserVerificationService $verification
     */
    public function __construct(
        TokenService $tokenService,
        AuthService $auth,
        UserUrlDetectorInterface $urlDetector,
        UserVerificationService $verification
    ) {
        parent::__construct($tokenService, $auth, $urlDetector);

        $this->verification = $verification;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return void
     */
    protected function processValid(UserInterface $user): void
    {
        // Confirm user email if not verified
        $this->verification->confirmUser($user);
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
