<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Action\PostRequestActionInterface;
use BetaKiller\Auth\IncorrectCredentialsException;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

/**
 * Class RegularLoginAction
 * Action for logging in via regular auth form
 *
 * @package BetaKiller\Auth
 */
class RegularLoginAction extends AbstractAction implements PostRequestActionInterface
{
    private const ARG_LOGIN    = 'user-login';
    private const ARG_PASSWORD = 'user-password';

    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * RegularLoginAction constructor.
     *
     * @param \BetaKiller\Service\AuthService                $auth
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     */
    public function __construct(AuthService $auth, UserRepositoryInterface $userRepo)
    {
        $this->auth     = $auth;
        $this->userRepo = $userRepo;
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     */
    public function definePostArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_LOGIN)
            ->string(self::ARG_PASSWORD);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\InactiveException
     * @throws \BetaKiller\Auth\IncorrectCredentialsException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // HTTP referrer is required to proceed
        $referrer = ServerRequestHelper::getHttpReferrer($request);

        // Stop script-kiddies
        if (!$referrer) {
            throw new BadRequestHttpException('Missing ref');
        }

        $post = ActionRequestHelper::postArguments($request);

        // Sanitize
        $login    = $post->getString(self::ARG_LOGIN);
        $password = $post->getString(self::ARG_PASSWORD);

        if (!$login || !$password) {
            throw new BadRequestHttpException('No username or password sent');
        }

        $user = $this->userRepo->searchBy($login);

        if (!$user) {
            throw new IncorrectCredentialsException;
        }

        // If the passwords match, perform a login
        if (!$this->auth->checkPassword($user, $password)) {
            throw new IncorrectCredentialsException;
        }

        $session = ServerRequestHelper::getSession($request);
        $this->auth->login($session, $user);

        return ServerRequestHelper::isJsonPreferred($request)
            ? ResponseHelper::successJson()
            : ResponseHelper::redirect($referrer); // Fallback for non-JS browsers
    }
}
