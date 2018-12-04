<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepository;
use Psr\Http\Message\ServerRequestInterface;
use BetaKiller\Model\UserStatus;
use BetaKiller\Repository\UserStatusRepository;

abstract class AbstractVerificationEmailService
{
    public const NOTIFICATION_NAME = 'preregistration/verification/email';

    /**
     * @var \BetaKiller\Service\TokenService
     */
    private $tokenService;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Repository\UserStatusRepository
     */
    private $accStatusRepo;

    /**
     * @param \BetaKiller\Helper\NotificationHelper       $notificationHelper
     * @param \BetaKiller\Service\TokenService            $tokenService
     * @param \BetaKiller\Repository\UserStatusRepository $accStatusRepo
     * @param \BetaKiller\Repository\UserRepository       $userRepo
     */
    public function __construct(
        NotificationHelper $notificationHelper,
        TokenService $tokenService,
        UserStatusRepository $accStatusRepo,
        UserRepository $userRepo
    ) {
        $this->tokenService  = $tokenService;
        $this->notification  = $notificationHelper;
        $this->userRepo      = $userRepo;
        $this->accStatusRepo = $accStatusRepo;
    }

    /**
     * @return string
     */
    abstract protected function getTokenPeriod(): string;

    /**
     * @return string
     */
    abstract protected function getActionEntityCodename(): string;

    /**
     * @return string
     */
    abstract protected function getAppEntityCodename(): string;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \BetaKiller\Model\UserInterface          $userModel
     *
     * @return array
     */
    abstract protected function getEmailData(ServerRequestInterface $request, UserInterface $userModel): array;

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function confirm(UserInterface $userModel): void
    {
        if (!$userModel->isEmailConfirmed()) {
            $statusConfirmed = $this->accStatusRepo->getByCodename(UserStatus::STATUS_CONFIRMED);
            $userModel->setStatus($statusConfirmed);
            $this->userRepo->save($userModel);
        }
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \BetaKiller\Model\UserInterface          $userModel
     *
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function sendEmail(ServerRequestInterface $request, UserInterface $userModel): void
    {
        $ttl        = new \DateInterval($this->getTokenPeriod());
        $tokenModel = $this->tokenService->create($userModel, $ttl);
        $actionUrl  = $this->getActionUrl($request, $tokenModel);
        $appUrl     = $this->getAppUrl($request);

        $emailData = [
            'action_url' => $actionUrl,
            'app_url'    => $appUrl,
        ];

        $emailDataAdd = $this->getEmailData($request, $userModel);
        if ($emailDataAdd) {
            $emailData = array_merge($emailData, $emailDataAdd);
        }

        $this->notification->directMessage(self::NOTIFICATION_NAME, $userModel, $emailData);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \BetaKiller\Model\TokenInterface         $tokenModel
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getActionUrl(ServerRequestInterface $request, TokenInterface $tokenModel): string
    {
        $urlHelper        = ServerRequestHelper::getUrlHelper($request);
        $actionUrlElement = $urlHelper->getUrlElementByCodename($this->getActionEntityCodename());
        $actionUrlParams  = $urlHelper->createUrlContainer()->setEntity($tokenModel);

        return $urlHelper->makeUrl($actionUrlElement, $actionUrlParams, false);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getAppUrl(ServerRequestInterface $request): string
    {
        return $this->makeEntityUrl($request, $this->getAppEntityCodename());
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string                                   $entityCodename
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    protected function makeEntityUrl(ServerRequestInterface $request, string $entityCodename): string
    {
        $urlHelper  = ServerRequestHelper::getUrlHelper($request);
        $urlElement = $urlHelper->getUrlElementByCodename($entityCodename);

        return $urlHelper->makeUrl($urlElement);
    }
}
