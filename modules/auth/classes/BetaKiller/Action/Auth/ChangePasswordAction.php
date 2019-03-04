<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\IFace\Auth\PasswordChangeIFace;
use BetaKiller\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class ChangePasswordAction extends AbstractAction
{
    private const ARG_PASS = 'password';

    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * ChangePasswordAction constructor.
     *
     * @param \BetaKiller\Service\AuthService $auth
     */
    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Arguments definition for request` GET data
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition();
    }

    /**
     * Arguments definition for request` POST data
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function postArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->string(self::ARG_PASS)
            ->lengthBetween(AuthService::PASSWORD_MIN_LENGTH, AuthService::PASSWORD_MAX_LENGTH);
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

        // Update password
        $password = ActionRequestHelper::postArguments($request)->getString(self::ARG_PASS);

        $this->auth->updateUserPassword($user, $password);

        // Mark password as changed
        ServerRequestHelper::getFlash($request)->flash(PasswordChangeIFace::FLASH_STATUS, true);

        // Redirect back to IFace
        $nextElement = $urlHelper->getUrlElementByCodename(PasswordChangeIFace::codename());

        return ResponseHelper::redirect($urlHelper->makeUrl($nextElement));
    }
}
