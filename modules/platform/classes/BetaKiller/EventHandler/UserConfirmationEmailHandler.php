<?php
declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Action\Auth\ClaimRegistrationAction;
use BetaKiller\Event\AbstractUserWorkflowEvent;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Service\TokenService;

final class UserConfirmationEmailHandler
{
    public const EMAIL_VERIFICATION = 'email/user/verification';

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Service\TokenService
     */
    private $tokenService;

    public function __construct(
        NotificationHelper $notificationHelper,
        TokenService $tokenService,
        UrlHelperFactory $urlHelperFactory
    ) {
        $this->tokenService = $tokenService;
        $this->notification = $notificationHelper;
        $this->urlHelper    = $urlHelperFactory->create();
    }

    public function __invoke(AbstractUserWorkflowEvent $event): void
    {
        $user = $event->getUser();

        $token = $this->tokenService->create($user, new \DateInterval('P14D'));

        $emailData = [
            'claim_url' => $this->urlHelper->makeCodenameUrl(ClaimRegistrationAction::codename()),
            // For action URL generation
            '$token'    => $token,
        ];

        $this->notification->directMessage(self::EMAIL_VERIFICATION, $user, $emailData);
    }
}
