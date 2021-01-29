<?php
declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Event\AbstractUserWorkflowEvent;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Service\TokenService;

final class UserConfirmationEmailHandler
{
    public const EMAIL_VERIFICATION = 'email/user/verification';

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
        if ($user->isEmailConfirmed()) {
            return;
        }

        $token = $this->tokenService->create($user, new \DateInterval('P14D'));

        $emailData = [
            // For action URL generation
            '$token' => $token,
        ];

        $this->notification->directMessage(self::EMAIL_VERIFICATION, $user, $emailData);
    }
}
