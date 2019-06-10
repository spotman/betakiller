<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Service\TokenService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

abstract class AbstractTokenVerificationAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Service\TokenService
     */
    private $tokenService;

    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * @param \BetaKiller\Service\TokenService          $tokenService
     * @param \BetaKiller\Service\AuthService           $auth
     * @param \BetaKiller\Auth\UserUrlDetectorInterface $urlDetector
     */
    public function __construct(
        TokenService $tokenService,
        AuthService $auth,
        UserUrlDetectorInterface $urlDetector
    ) {
        $this->tokenService = $tokenService;
        $this->auth         = $auth;
        $this->urlDetector  = $urlDetector;
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

        if ($user->isBlocked()) {
            // Mark token as used
            $this->tokenService->used($token);

            throw new AccessDeniedException('Blocked user is trying to verify OTP token');
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
        SessionHelper::setTokenHash($session, $token);

        return $response;
    }

    /**
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string
     */
    protected function getSuccessUrl(UrlHelper $urlHelper, UserInterface $user): string
    {
        return $this->urlDetector->detect($user, $urlHelper);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return void
     */
    abstract protected function processValid(UserInterface $user): void;
}
