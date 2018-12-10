<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepository;
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
     * @var \BetaKiller\Helper\UrlHelper
     */
    protected $urlHelper;

    /**
     * @param \BetaKiller\Helper\NotificationHelper       $notificationHelper
     * @param \BetaKiller\Service\TokenService            $tokenService
     * @param \BetaKiller\Repository\UserStatusRepository $accStatusRepo
     * @param \BetaKiller\Repository\UserRepository       $userRepo
     * @param \BetaKiller\Factory\UrlHelperFactory        $urlHelperFactory
     */
    public function __construct(
        NotificationHelper $notificationHelper,
        TokenService $tokenService,
        UserStatusRepository $accStatusRepo,
        UserRepository $userRepo,
        UrlHelperFactory $urlHelperFactory
    ) {
        $this->tokenService  = $tokenService;
        $this->notification  = $notificationHelper;
        $this->userRepo      = $userRepo;
        $this->accStatusRepo = $accStatusRepo;
        $this->urlHelper     = $urlHelperFactory->create();
    }

    /**
     * @return \DateInterval
     */
    abstract protected function getTokenPeriod(): \DateInterval;

    /**
     * @return string
     */
    abstract protected function getActionEntityCodename(): string;

    /**
     * @return string
     */
    abstract protected function getAppEntityCodename(): string;

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return array
     */
    abstract protected function getEmailData(UserInterface $userModel): array;

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
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function sendEmail(UserInterface $userModel): void
    {
        $ttl        = $this->getTokenPeriod();
        $tokenModel = $this->tokenService->create($userModel, $ttl);
        $actionUrl  = $this->getActionUrl($tokenModel);
        $appUrl     = $this->getAppUrl();

        $emailData = [
            'action_url' => $actionUrl,
            'app_url'    => $appUrl,
        ];

        $emailDataAdd = $this->getEmailData($userModel);
        if ($emailDataAdd) {
            $emailData = array_merge($emailData, $emailDataAdd);
        }

        $this->notification->directMessage(self::NOTIFICATION_NAME, $userModel, $emailData);
    }

    /**
     * @param \BetaKiller\Model\TokenInterface $tokenModel
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getActionUrl(TokenInterface $tokenModel): string
    {
        $actionUrlElement = $this->urlHelper->getUrlElementByCodename($this->getActionEntityCodename());
        $actionUrlParams  = $this->urlHelper->createUrlContainer()->setEntity($tokenModel);

        return $this->urlHelper->makeUrl($actionUrlElement, $actionUrlParams, false);
    }

    /**
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getAppUrl(): string
    {
        return $this->makeEntityUrl($this->getAppEntityCodename());
    }

    /**
     * @param string $entityCodename
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    protected function makeEntityUrl(string $entityCodename): string
    {
        $urlElement = $this->urlHelper->getUrlElementByCodename($entityCodename);

        return $this->urlHelper->makeUrl($urlElement);
    }
}
