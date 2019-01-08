<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;

abstract class AbstractRecoveryAccessService
{
    public const NOTIFICATION_NAME = 'recovery-access';

    /**
     * @var \BetaKiller\Service\TokenService
     */
    private $tokenService;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @param \BetaKiller\Helper\NotificationHelper $notificationHelper
     * @param \BetaKiller\Service\TokenService      $tokenService
     */
    public function __construct(
        NotificationHelper $notificationHelper,
        TokenService $tokenService
    ) {
        $this->tokenService = $tokenService;
        $this->notification = $notificationHelper;
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
     * @param \BetaKiller\Model\UserInterface $userModel
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     *
     * @return array
     */
    abstract protected function getEmailData(UserInterface $userModel, UrlHelper $urlHelper): array;

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function sendEmail(UserInterface $userModel, UrlHelper $urlHelper): void
    {
        $ttl        = $this->getTokenPeriod();
        $tokenModel = $this->tokenService->create($userModel, $ttl);
        $actionUrl  = $this->getActionUrl($tokenModel, $urlHelper);

        $emailData = [
            'action_url' => $actionUrl,
        ];

        $emailDataAdd = $this->getEmailData($userModel, $urlHelper);
        if ($emailDataAdd) {
            $emailData = array_merge($emailData, $emailDataAdd);
        }

        $this->notification->directMessage(self::NOTIFICATION_NAME, $userModel, $emailData);
    }

    /**
     * @param \BetaKiller\Model\TokenInterface $tokenModel
     *
     * @param \BetaKiller\Helper\UrlHelper     $urlHelper
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getActionUrl(TokenInterface $tokenModel, UrlHelper $urlHelper): string
    {
        $actionUrlElement = $urlHelper->getUrlElementByCodename($this->getActionEntityCodename());
        $actionUrlParams  = $urlHelper->createUrlContainer()->setEntity($tokenModel);

        return $urlHelper->makeUrl($actionUrlElement, $actionUrlParams, false);
    }
}
