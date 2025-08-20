<?php

declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Event\AccessRecoveryRequestedEvent;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\Message\UserAccessRecoveryMessage;
use BetaKiller\Url\Container\UrlContainerInterface;

class AccessRecoveryService
{
    private const TOKEN_PERIOD = 'PT4H';

    /**
     * @var \BetaKiller\Service\TokenService
     */
    private TokenService $tokenService;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private NotificationHelper $notification;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private EventBusInterface $eventBus;

    /**
     * @param \BetaKiller\Helper\NotificationHelper    $notificationHelper
     * @param \BetaKiller\Service\TokenService         $tokenService
     * @param \BetaKiller\MessageBus\EventBusInterface $eventBus
     */
    public function __construct(
        NotificationHelper $notificationHelper,
        TokenService $tokenService,
        EventBusInterface $eventBus
    ) {
        $this->tokenService = $tokenService;
        $this->notification = $notificationHelper;
        $this->eventBus     = $eventBus;
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
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function sendEmail(UserInterface $user, UrlContainerInterface $urlParams): void
    {
        $ttl   = $this->getTokenPeriod();
        $token = $this->tokenService->create($user, $ttl);

        $this->notification->sendDirect($user, UserAccessRecoveryMessage::createFrom($token));

        $this->eventBus->emit(new AccessRecoveryRequestedEvent($user, $urlParams));
    }
}
