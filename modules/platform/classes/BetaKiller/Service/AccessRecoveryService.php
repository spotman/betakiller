<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Action\Auth\ClaimRegistrationAction;
use BetaKiller\Action\Auth\VerifyAccessRecoveryTokenAction;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\UserInterface;

class AccessRecoveryService
{
    public const NOTIFICATION_NAME = 'auth/access-recovery';

    private const TOKEN_PERIOD = 'PT4H';

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
    protected function getTokenPeriod(): \DateInterval
    {
        return new \DateInterval(self::TOKEN_PERIOD);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     *
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function sendEmail(UserInterface $user, UrlHelper $urlHelper): void
    {
        $ttl   = $this->getTokenPeriod();
        $token = $this->tokenService->create($user, $ttl);

        $actionElement = $urlHelper->getUrlElementByCodename(VerifyAccessRecoveryTokenAction::codename());
        $actionParams  = $urlHelper->createUrlContainer()->setEntity($token);

        $claimElement = $urlHelper->getUrlElementByCodename(ClaimRegistrationAction::codename());

        $this->notification->directMessage(self::NOTIFICATION_NAME, $user, [
            'recovery_url' => $urlHelper->makeUrl($actionElement, $actionParams, false),
            'claim_url'    => $urlHelper->makeUrl($claimElement, null, false),
        ]);
    }
}
