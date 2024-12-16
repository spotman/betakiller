<?php

declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Workflow\UserWorkflow;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class SuspendAction extends AbstractAction
{
    /**
     * SuspendApiMethod constructor.
     *
     * @param \BetaKiller\Workflow\UserWorkflow         $userWorkflow
     * @param \BetaKiller\Auth\UserUrlDetectorInterface $urlDetector
     */
    public function __construct(
        private UserWorkflow $userWorkflow,
        private UserUrlDetectorInterface $urlDetector
    ) {
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // No requests for other users here, only for caller
        $user = ServerRequestHelper::getUser($request);

        $this->userWorkflow->suspend($user);

        // Redirect to proper page
        $url = $this->urlDetector->detect($user);

        return ResponseHelper::redirect($url);
    }
}
