<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\AuthFacade;
use BetaKiller\Auth\IncorrectPasswordException;
use BetaKiller\Auth\UserDoesNotExistsException;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RegularLoginAction
 * Action for logging in via regular auth form
 *
 * @package BetaKiller\Auth
 */
class RegularLoginAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Auth\AuthFacade
     */
    private $auth;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * RegularLoginAction constructor.
     *
     * @param \BetaKiller\Auth\AuthFacade           $auth
     * @param \BetaKiller\Repository\UserRepository $userRepo
     */
    public function __construct(AuthFacade $auth, UserRepository $userRepo)
    {
        $this->auth     = $auth;
        $this->userRepo = $userRepo;
    }

    /**
     * Handle the request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Auth\IncorrectPasswordException
     * @throws \BetaKiller\Auth\UserDoesNotExistsException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // HTTP referrer is required to proceed
        $referrer = ServerRequestHelper::getHttpReferrer($request);

        $post = ServerRequestHelper::getPost($request);

        $userLogin    = $post['user-login'] ?? null;
        $userPassword = $post['user-password'] ?? null;

        // Sanitize
        $userLogin    = trim(\HTML::chars($userLogin));
        $userPassword = trim(\HTML::chars($userPassword));

        if (!$userLogin || !$userPassword) {
            throw new BadRequestHttpException('No username or password sent');
        }

        $user = $this->userRepo->searchBy($userLogin);

        if (!$user) {
            throw new UserDoesNotExistsException;
        }

        // If the passwords match, perform a login
        if (!$this->auth->checkPassword($user, $userPassword)) {
            throw new IncorrectPasswordException();
        }

        $response = ServerRequestHelper::isJsonPreferred($request)
            ? ResponseHelper::successJson()
            : ResponseHelper::redirect($referrer); // Fallback for non-JS browsers

        return $this->auth->login($user, $request, $response);
    }
}
