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

readonly class SessionRestartAction extends AbstractAction
{
    /**
     * SessionRestartAction constructor.
     *
     * @param \BetaKiller\Service\AuthService $auth
     */
    public function __construct(private AuthService $auth)
    {
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

        return ResponseHelper::redirect(
            $urlHelper->makeCodenameUrl(AuthRootIFace::codename())
        );
    }
}
