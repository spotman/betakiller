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

final readonly class ActivateSuspendedAction extends AbstractAction
{
    /**
     * ActivateSuspendedAction constructor.
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
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = ServerRequestHelper::getUser($request);

        $this->userWorkflow->resumeSuspended($user);

        // Redirect to actual page
        $url = $this->urlDetector->detect($user);

        return ResponseHelper::redirect($url);
    }
}
