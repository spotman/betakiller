<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\AuthFacade;
use BetaKiller\Auth\IncorrectPasswordException;
use BetaKiller\Auth\UserDoesNotExistsException;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\LoginIFace;
use BetaKiller\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

/**
 * Class RegularLoginAction
 * Action for logging in via regular auth form
 *
 * @package BetaKiller\Auth
 */
class RegularLoginAction extends AbstractAction
{
    public const  URL          = LoginIFace::URL.'regular';
    private const ARG_LOGIN    = 'user-login';
    private const ARG_PASSWORD = 'user-password';


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
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition();
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function postArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->string(self::ARG_LOGIN)
            ->string(self::ARG_PASSWORD);
    }

    /**
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

        $post = ActionRequestHelper::postArguments($request);

        // Sanitize
        $userLogin    = $post->getString(self::ARG_LOGIN);
        $userPassword = $post->getString(self::ARG_PASSWORD);

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
