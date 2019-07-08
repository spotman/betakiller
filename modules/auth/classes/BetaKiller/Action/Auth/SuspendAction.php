<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\UserStatus;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Repository\UserStatusRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SuspendAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Repository\UserStatusRepositoryInterface
     */
    private $statusRepo;

    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * SuspendApiMethod constructor.
     *
     * @param \BetaKiller\Repository\UserStatusRepositoryInterface $statusRepo
     * @param \BetaKiller\Repository\UserRepositoryInterface       $userRepo
     * @param \BetaKiller\Auth\UserUrlDetectorInterface            $urlDetector
     */
    public function __construct(
        UserStatusRepositoryInterface $statusRepo,
        UserRepositoryInterface $userRepo,
        UserUrlDetectorInterface $urlDetector
    ) {
        $this->statusRepo  = $statusRepo;
        $this->urlDetector = $urlDetector;
        $this->userRepo    = $userRepo;
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
        $user      = ServerRequestHelper::getUser($request);

        // Update status
        $status = $this->statusRepo->getByCodename(UserStatus::STATUS_SUSPENDED);

        $user->setStatus($status);
        $this->userRepo->save($user);

        // Redirect to proper page
        $url = $this->urlDetector->detect($user);

        return ResponseHelper::redirect($url);
    }
}
