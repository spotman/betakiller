<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\AccessRecoveryRequestIFace;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Service\AccessRecoveryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class SendRecoveryEmailAction extends AbstractAction
{
    private const ARG_EMAIL = 'email';

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Service\AccessRecoveryService
     */
    private $recovery;

    /**
     * @param \BetaKiller\Repository\UserRepository     $userRepo
     * @param \BetaKiller\Service\AccessRecoveryService $recovery
     */
    public function __construct(
        UserRepository $userRepo,
        AccessRecoveryService $recovery
    ) {
        $this->userRepo = $userRepo;
        $this->recovery = $recovery;
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
        return $this->definition()
            ->email(self::ARG_EMAIL);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $post      = ActionRequestHelper::postArguments($request);
        $flash     = ServerRequestHelper::getFlash($request);

        $requestIFace = $urlHelper->getUrlElementByCodename(AccessRecoveryRequestIFace::codename());
        $response     = ResponseHelper::redirect($urlHelper->makeUrl($requestIFace));

        // checking email on duplicate
        $email = $post->getString(self::ARG_EMAIL);

        $user = $this->userRepo->searchBy($email);

        // TODO Separate status and message for blocked users
        if (!$user || $user->isBlocked()) {
            $flash->flash(AccessRecoveryRequestIFace::FLASH_STATUS, AccessRecoveryRequestIFace::FLASH_STATUS_MISSING);

            return $response;
        }

        $this->recovery->sendEmail($user, $urlHelper);

        $flash->flash(AccessRecoveryRequestIFace::FLASH_STATUS, AccessRecoveryRequestIFace::FLASH_STATUS_OK);

        return $response;
    }
}
