<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Workflow\UserWorkflow;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ActivateSuspendedAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * @var \BetaKiller\Workflow\UserWorkflow
     */
    private $userWorkflow;

    /**
     * ActivateSuspendedAction constructor.
     *
     * @param \BetaKiller\Workflow\UserWorkflow              $userWorkflow
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Auth\UserUrlDetectorInterface      $urlDetector
     */
    public function __construct(
        UserWorkflow $userWorkflow,
        UserRepositoryInterface $userRepo,
        UserUrlDetectorInterface $urlDetector
    ) {
        $this->userRepo     = $userRepo;
        $this->urlDetector  = $urlDetector;
        $this->userWorkflow = $userWorkflow;
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

        $this->userWorkflow->activateSuspended($user);

        $this->userRepo->save($user);

        // Redirect to actual page
        $url = $this->urlDetector->detect($user);

        return ResponseHelper::redirect($url);
    }
}
