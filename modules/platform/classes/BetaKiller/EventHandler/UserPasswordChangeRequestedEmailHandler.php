<?php
declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Event\UserPasswordChangeRequestedEvent;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Service\TokenService;

final class UserPasswordChangeRequestedEmailHandler
{
    public const REQUEST_PASSWORD_CHANGE = 'email/user/password-change-request';

    /**
     * @var \BetaKiller\Service\TokenService
     */
    private TokenService $tokenService;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private NotificationHelper $notification;

    public function __construct(
        NotificationHelper $notification,
        TokenService $tokenService
    ) {
        $this->notification = $notification;
        $this->tokenService = $tokenService;
    }

    public function __invoke(UserPasswordChangeRequestedEvent $event): void
    {
        $user  = $event->getUser();
        $token = $this->tokenService->create($user, new \DateInterval('PT8H'));

        $this->notification->directMessage(self::REQUEST_PASSWORD_CHANGE, $user, [
            // For action url generation
            '$token' => $token,
        ]);
    }
}
