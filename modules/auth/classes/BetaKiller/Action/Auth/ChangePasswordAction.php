<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Action\PostRequestActionInterface;
use BetaKiller\Exception\PublicException;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\IFace\Auth\PasswordChangeIFace;
use BetaKiller\Security\CsrfService;
use BetaKiller\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class ChangePasswordAction extends AbstractAction implements PostRequestActionInterface
{
    private const ARG_PASS = 'password';
    private const ARG_PASS_CONFIRM = 'password_confirm';

    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * @var \BetaKiller\Security\CsrfService
     */
    private $csrf;

    /**
     * ChangePasswordAction constructor.
     *
     * @param \BetaKiller\Service\AuthService $auth
     */
    public function __construct(AuthService $auth, CsrfService $csrf)
    {
        $this->auth = $auth;
        $this->csrf = $csrf;
    }

    /**
     * Arguments definition for request` POST data
     *
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     */
    public function definePostArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_PASS)
            ->lengthBetween(AuthService::PASSWORD_MIN_LENGTH, AuthService::PASSWORD_MAX_LENGTH)
            //
            ->string(self::ARG_PASS_CONFIRM)
            ->lengthBetween(AuthService::PASSWORD_MIN_LENGTH, AuthService::PASSWORD_MAX_LENGTH)
            //
            ->import($this->csrf);
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session   = ServerRequestHelper::getSession($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $user      = ServerRequestHelper::getUser($request);

        // Ensure user had been authorized via one-time token
        SessionHelper::checkToken($session);

        $this->csrf->checkActionToken($request);

        $post = ActionRequestHelper::postArguments($request);

        // Update password
        $password = $post->getString(self::ARG_PASS);
        $confirm = $post->getString(self::ARG_PASS_CONFIRM);

        if ($password !== $confirm) {
            throw new PublicException('Passwords are not equal');
        }

        $this->auth->updateUserPassword($user, $password);

        $this->csrf->clearActionToken($request);

        // Mark password as changed
        ServerRequestHelper::getFlash($request)->flash(PasswordChangeIFace::FLASH_STATUS, true);

        // Redirect back to IFace
        return ResponseHelper::redirect($urlHelper->makeCodenameUrl(PasswordChangeIFace::codename()));
    }
}
