<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth\Admin;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\Admin\AuthRootIFace;
use BetaKiller\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class SessionRestartAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * SessionRestartAction constructor.
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
        return $this->definition();
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\BadRequestHttpException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (ServerRequestHelper::isGuest($request)) {
            throw new BadRequestHttpException;
        }

        $user      = ServerRequestHelper::getUser($request);
        $session   = ServerRequestHelper::getSession($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $this->auth->login($session, $user);

        $element = $urlHelper->getUrlElementByCodename(AuthRootIFace::codename());

        return ResponseHelper::redirect(
            $urlHelper->makeUrl($element)
        );
    }
}
