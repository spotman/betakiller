<?php
declare(strict_types=1);

namespace BetaKiller\Action\App;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\AuthFacade;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;
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
     * @var \BetaKiller\Auth\AuthFacade
     */
    private $authFacade;

    /**
     * @param \BetaKiller\Service\TokenService $tokenService
     * @param \BetaKiller\Auth\AuthFacade      $authFacade
     */
    public function __construct(
        TokenService $tokenService,
        AuthFacade $authFacade
    ) {
        $this->tokenService = $tokenService;
        $this->authFacade   = $authFacade;
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
        /** @var \BetaKiller\Model\TokenInterface $token */
        $token = ServerRequestHelper::getEntity($request, TokenInterface::class);

        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $user = $token->getUser();

        $isValid = $this->tokenService->check($token);

        $redirectUrl = $isValid
            ? $this->getSuccessUrl($urlHelper, $user)
            : $this->getInactiveTokenUrl($urlHelper, $user);

        $response = ResponseHelper::redirect($redirectUrl);

        if ($isValid) {
            $this->processValid($user);

            $isGuest = ServerRequestHelper::isGuest($request);

            if ($isGuest) {
                $this->authFacade->login($user, $request, $response);
            }
        }

        return $response;
    }

    /**
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string
     */
    abstract protected function getInactiveTokenUrl(UrlHelper $urlHelper, UserInterface $user): string;

    /**
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string
     */
    abstract protected function getSuccessUrl(UrlHelper $urlHelper, UserInterface $user): string;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return void
     */
    abstract protected function processValid(UserInterface $user): void;
}
