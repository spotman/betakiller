<?php
namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\AuthFacade;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class LogoutAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Auth\AuthFacade
     */
    private $auth;

    /**
     * LogoutAction constructor.
     *
     * @param \BetaKiller\Auth\AuthFacade $auth
     */
    public function __construct(AuthFacade $auth)
    {
        $this->auth = $auth;
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
        return $this->definition();
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Redirect to site index
        $response = ResponseHelper::redirect('/');

        // Sign out the user and get fresh session
        return $this->auth->logout($request, $response);
    }
}
