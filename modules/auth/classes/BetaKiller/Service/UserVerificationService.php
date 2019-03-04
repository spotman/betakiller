<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Action\Auth\ClaimRegistrationAction;
use BetaKiller\Action\Auth\ConfirmEmailAction;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Model\UserStatus;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Repository\UserStatusRepositoryInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

class UserVerificationService
{
    public const NOTIFICATION_NAME = 'auth/verification';

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
     * @var \BetaKiller\Repository\UserStatusRepositoryInterface
     */
    private $accStatusRepo;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @param \BetaKiller\Helper\NotificationHelper                $notificationHelper
     * @param \BetaKiller\Service\TokenService                     $tokenService
     * @param \BetaKiller\Repository\UserStatusRepositoryInterface $accStatusRepo
     * @param \BetaKiller\Repository\UserRepository                $userRepo
     * @param \BetaKiller\Factory\UrlHelperFactory                 $urlHelperFactory
     */
    public function __construct(
        NotificationHelper $notificationHelper,
        TokenService $tokenService,
        UserStatusRepositoryInterface $accStatusRepo,
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
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return void
     * @throws \BetaKiller\Exception\ValidationException
     */
    public function confirmUser(UserInterface $user): void
    {
        if (!$user->isEmailConfirmed()) {
            $statusConfirmed = $this->accStatusRepo->getByCodename(UserStatus::STATUS_CONFIRMED);
            $user->setStatus($statusConfirmed);
            $this->userRepo->save($user);
        }
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function sendEmail(UserInterface $user): void
    {
        $ttl   = $this->getTokenPeriod();
        $token = $this->tokenService->create($user, $ttl);

        $params = $this->urlHelper->createUrlContainer()->setEntity($token);

        $emailData = array_merge($this->getAdditionalEmailData($this->urlHelper), [
            'action_url' => $this->getActionUrl($this->urlHelper, $params),
        ]);

        $this->notification->directMessage(self::NOTIFICATION_NAME, $user, $emailData);
    }

    /**
     * @return \DateInterval
     * @throws \Exception
     */
    protected function getTokenPeriod(): \DateInterval
    {
        return new \DateInterval('P14D');
    }

    /**
     * @param \BetaKiller\Helper\UrlHelper $urlHelper
     *
     * @return array
     */
    protected function getAdditionalEmailData(UrlHelper $urlHelper): array
    {
        $abuseElement = $urlHelper->getUrlElementByCodename(ClaimRegistrationAction::codename());

        return [
            'claim_url' => $urlHelper->makeUrl($abuseElement),
        ];
    }

    /**
     * @param \BetaKiller\Helper\UrlHelper                    $urlHelper
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    protected function getActionUrl(UrlHelper $urlHelper, UrlContainerInterface $params): string
    {
        $actionUrlElement = $urlHelper->getUrlElementByCodename(ConfirmEmailAction::codename());

        return $urlHelper->makeUrl($actionUrlElement, $params, false);
    }
}
