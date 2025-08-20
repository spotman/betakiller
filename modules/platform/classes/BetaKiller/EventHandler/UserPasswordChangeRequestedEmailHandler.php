<?php

declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Event\UserPasswordChangeRequestedEvent;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Notification\Message\UserPasswordChangeRequestMessage;
use BetaKiller\Service\TokenService;
use DateInterval;

final readonly class UserPasswordChangeRequestedEmailHandler
{
    public function __construct(
        private NotificationHelper $notification,
        private TokenService $tokenService
    ) {
    }

    public function __invoke(UserPasswordChangeRequestedEvent $event): void
    {
        $user  = $event->getUser();
        $token = $this->tokenService->create($user, new DateInterval('PT8H'));

        $this->notification->sendDirect($user, UserPasswordChangeRequestMessage::createFrom($token));
    }
}
