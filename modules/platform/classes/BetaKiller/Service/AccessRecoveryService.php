<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Action\Auth\ClaimRegistrationAction;
use BetaKiller\Action\Auth\VerifyAccessRecoveryTokenAction;
use BetaKiller\Event\AccessRecoveryRequestedEvent;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

class AccessRecoveryService
{
    public const NOTIFICATION_NAME = 'email/user/access-recovery';

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
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private $eventBus;

    /**
     * @param \BetaKiller\Helper\NotificationHelper    $notificationHelper
     * @param \BetaKiller\Service\TokenService         $tokenService
     * @param \BetaKiller\Factory\UrlHelperFactory     $urlHelperFactory
     * @param \BetaKiller\MessageBus\EventBusInterface $eventBus
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(
        NotificationHelper $notificationHelper,
        TokenService $tokenService,
        UrlHelperFactory $urlHelperFactory,
        EventBusInterface $eventBus
    ) {
        $this->tokenService = $tokenService;
        $this->notification = $notificationHelper;
        $this->eventBus     = $eventBus;
        $this->urlHelper    = $urlHelperFactory->create();
    }

    /**
     * @return \DateInterval
     */
    protected function getTokenPeriod(): \DateInterval
    {
        return new \DateInterval(self::TOKEN_PERIOD);
    }

    /**
     * @param \BetaKiller\Model\UserInterface                 $user
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParams
     *
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function sendEmail(UserInterface $user, UrlContainerInterface $urlParams): void
    {
        $ttl   = $this->getTokenPeriod();
        $token = $this->tokenService->create($user, $ttl);

        $actionElement = $this->urlHelper->getUrlElementByCodename(VerifyAccessRecoveryTokenAction::codename());
        $actionParams  = $this->urlHelper->createUrlContainer()->setEntity($token);

        $claimElement = $this->urlHelper->getUrlElementByCodename(ClaimRegistrationAction::codename());

        $this->notification->directMessage(self::NOTIFICATION_NAME, $user, [
            // User Language will be fetched from Token
            'recovery_url' => $this->urlHelper->makeUrl($actionElement, $actionParams, false),
            'claim_url'    => $this->urlHelper->makeUrl($claimElement, $actionParams, false),
        ]);

        $this->eventBus->emit(new AccessRecoveryRequestedEvent($user, $urlParams));
    }
}
