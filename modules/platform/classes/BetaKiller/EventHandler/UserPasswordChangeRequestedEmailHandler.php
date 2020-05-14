<?php
declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Action\Auth\ClaimRegistrationAction;
use BetaKiller\Event\UserPasswordChangeRequestedEvent;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Service\TokenService;

final class UserPasswordChangeRequestedEmailHandler
{
    public const REQUEST_PASSWORD_CHANGE = 'email/user/password-change-request';

    /**
     * @var \BetaKiller\Helper\UrlHelperInterface
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\Service\TokenService
     */
    private $tokenService;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    public function __construct(
        NotificationHelper $notification,
        TokenService $tokenService,
        UrlHelperFactory $urlHelperFactory
    ) {
        $this->notification = $notification;
        $this->tokenService = $tokenService;
        $this->urlHelper    = $urlHelperFactory->create();
    }

    public function __invoke(UserPasswordChangeRequestedEvent $event): void
    {
        $user  = $event->getUser();
        $token = $this->tokenService->create($user, new \DateInterval('PT8H'));

        $this->notification->directMessage(self::REQUEST_PASSWORD_CHANGE, $user, [
            // For action url generation
            '$token'    => $token,
            'claim_url' => $this->urlHelper->makeCodenameUrl(ClaimRegistrationAction::codename()),
        ]);
    }
}
