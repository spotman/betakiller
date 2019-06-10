<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\UserStatus;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Repository\UserStatusRepositoryInterface;
use BetaKiller\Service\UserVerificationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class ActivateSuspendedAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Repository\UserStatusRepositoryInterface
     */
    private $statusRepo;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * @var \BetaKiller\Service\UserVerificationService
     */
    private $verification;

    /**
     * ActivateSuspendedAction constructor.
     *
     * @param \BetaKiller\Repository\UserStatusRepositoryInterface $statusRepo
     * @param \BetaKiller\Repository\UserRepository                $userRepo
     * @param \BetaKiller\Auth\UserUrlDetectorInterface            $urlDetector
     * @param \BetaKiller\Service\UserVerificationService          $verification
     */
    public function __construct(
        UserStatusRepositoryInterface $statusRepo,
        UserRepository $userRepo,
        UserUrlDetectorInterface $urlDetector,
        UserVerificationService $verification
    ) {
        $this->statusRepo   = $statusRepo;
        $this->userRepo     = $userRepo;
        $this->urlDetector  = $urlDetector;
        $this->verification = $verification;
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
        $user      = ServerRequestHelper::getUser($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        // Update status to "created" to prevent status workflow hacks (created => suspended => confirmed)
        $status = $this->statusRepo->getByCodename(UserStatus::STATUS_CREATED);

        $user->setStatus($status);

        $this->userRepo->save($user);

        // Send verification link
        $this->verification->sendEmail($user);

        // Redirect to actual page
        $url = $this->urlDetector->detect($user, $urlHelper);

        return ResponseHelper::redirect($url);
    }
}
