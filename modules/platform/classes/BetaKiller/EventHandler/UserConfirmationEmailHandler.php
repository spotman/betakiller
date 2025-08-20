<?php

declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Event\AbstractUserWorkflowEvent;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Notification\Message\UserVerificationMessage;
use BetaKiller\Service\TokenService;
use DateInterval;

final class UserConfirmationEmailHandler
{
    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private NotificationHelper $notification;

    /**
     * @var \BetaKiller\Service\TokenService
     */
    private TokenService $tokenService;

    public function __construct(
        NotificationHelper $notificationHelper,
        TokenService $tokenService
    ) {
        $this->tokenService = $tokenService;
        $this->notification = $notificationHelper;
    }

    public function __invoke(AbstractUserWorkflowEvent $event): void
    {
        $user = $event->getUser();

        // Skip confirmation messages for
        if ($user->isEmailVerified()) {
            return;
        }

        $token = $this->tokenService->create($user, new DateInterval('P14D'));

        $this->notification->sendDirect($user, UserVerificationMessage::createFrom($token));
    }
}
