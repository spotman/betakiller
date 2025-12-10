<?php

declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Auth\PasswordChangeIFace;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Service\TokenService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class AbstractTokenVerificationAction extends AbstractAction
{
    /**
     * @param \BetaKiller\Service\TokenService          $tokenService
     * @param \BetaKiller\Service\AuthService           $auth
     * @param \BetaKiller\Auth\UserUrlDetectorInterface $urlDetector
     */
    public function __construct(
        private TokenService $tokenService,
        private AuthService $auth,
        private UserUrlDetectorInterface $urlDetector
    ) {
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var \BetaKiller\Model\TokenInterface $token */
        $token = ServerRequestHelper::getEntity($request, TokenInterface::class);

        // Get token user
        $user = $token->getUser();

        if ($user->inStateBanned()) {
            // Mark token as used
            $this->tokenService->used($token);

            throw new AccessDeniedException('Blocked user is trying to verify OTP token');
        }

        if ($token->isUsed() && !$this->isTokenReuseAllowed()) {
            throw new AccessDeniedException('OTP tokens reuse is not allowed');
        }

        // Process user action
        $this->processValid($user);

        // Mark token as used
        $this->tokenService->used($token);

        // Make redirect URL
        $urlHelper   = ServerRequestHelper::getUrlHelper($request);
        $redirectUrl = $this->getSuccessUrl($urlHelper, $user);
        $response    = ResponseHelper::redirect($redirectUrl);

        // Force user login
        $session = ServerRequestHelper::getSession($request);
        $this->auth->login($session, $user);

        // Store token hash for further security checks
        SessionHelper::setVerificationToken($session, $token);

        return $response;
    }

    /**
     * @param \BetaKiller\Helper\UrlHelperInterface $urlHelper
     * @param \BetaKiller\Model\UserInterface       $user
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    protected function getSuccessUrl(UrlHelperInterface $urlHelper, UserInterface $user): string
    {
        // Redirect to password change IFace in case of empty password (initial password setting)
        if (!$user->hasPassword()) {
            return $urlHelper->makeCodenameUrl(PasswordChangeIFace::codename());
        }

        return $this->urlDetector->detect($user);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return void
     */
    abstract protected function processValid(UserInterface $user): void;

    /**
     * @return bool
     */
    abstract protected function isTokenReuseAllowed(): bool;
}
