<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Action\PostRequestActionInterface;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\AccessRecoveryRequestIFace;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Security\CsrfService;
use BetaKiller\Service\AccessRecoveryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class SendRecoveryEmailAction extends AbstractAction implements PostRequestActionInterface
{
    private const ARG_EMAIL = 'email';

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Service\AccessRecoveryService
     */
    private $recovery;

    /**
     * @var \BetaKiller\Security\CsrfService
     */
    private $csrf;

    /**
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Service\AccessRecoveryService      $recovery
     * @param \BetaKiller\Security\CsrfService               $csrf
     */
    public function __construct(
        UserRepositoryInterface $userRepo,
        AccessRecoveryService $recovery,
        CsrfService $csrf
    ) {
        $this->userRepo = $userRepo;
        $this->recovery = $recovery;
        $this->csrf     = $csrf;
    }

    public function definePostArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->email(self::ARG_EMAIL)
            //
            ->import($this->csrf);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $urlParams = ServerRequestHelper::getUrlContainer($request);
        $post      = ActionRequestHelper::postArguments($request);
        $flash     = ServerRequestHelper::getFlash($request);

        // Clear immediately coz there is a redirect
        $this->csrf->checkActionToken($request);
        $this->csrf->clearActionToken($request);

        $response = ResponseHelper::redirect($urlHelper->makeCodenameUrl(AccessRecoveryRequestIFace::codename()));

        // checking email on duplicate
        $email = $post->getString(self::ARG_EMAIL);

        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            $flash->flash(AccessRecoveryRequestIFace::FLASH_STATUS, AccessRecoveryRequestIFace::FLASH_STATUS_MISSING);

            return $response;
        }

        // Separate status and message for blocked users
        if ($user->isBlocked()) {
            $flash->flash(AccessRecoveryRequestIFace::FLASH_STATUS, AccessRecoveryRequestIFace::FLASH_STATUS_BLOCKED);

            return $response;
        }

        $this->recovery->sendEmail($user, $urlParams);

        $flash->flash(AccessRecoveryRequestIFace::FLASH_STATUS, AccessRecoveryRequestIFace::FLASH_STATUS_OK);

        return $response;
    }
}
