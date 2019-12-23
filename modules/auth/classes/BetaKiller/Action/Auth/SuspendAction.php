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

class SuspendAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Workflow\UserWorkflow
     */
    private $userWorkflow;

    /**
     * SuspendApiMethod constructor.
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
        $this->userWorkflow = $userWorkflow;
        $this->urlDetector  = $urlDetector;
        $this->userRepo     = $userRepo;
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

        $this->userRepo->save($user);

        // Redirect to proper page
        $url = $this->urlDetector->detect($user);

        return ResponseHelper::redirect($url);
    }
}
