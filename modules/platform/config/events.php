<?php
declare(strict_types=1);

use BetaKiller\Event\UserBlockedEvent;
use BetaKiller\Event\UserConfirmationEmailRequestedEvent;
use BetaKiller\Event\UserEmailChangedEvent;
use BetaKiller\Event\UserResumedEvent;
use BetaKiller\Event\UserSuspendedEvent;
use BetaKiller\Event\UserUnlockedEvent;
use BetaKiller\EventHandler\UserConfirmationEmailHandler;

return [
    UserBlockedEvent::class => [
        // Bind handlers here if needed
    ],

    UserUnlockedEvent::class => [
        // Bind handlers here if needed
    ],

    UserSuspendedEvent::class => [
        // Bind handlers here if needed
    ],

    UserResumedEvent::class => [
        // Confirmation Email
        UserConfirmationEmailHandler::class,
    ],

    UserEmailChangedEvent::class => [
        // New email => new verification
        UserConfirmationEmailHandler::class,
    ],

    UserConfirmationEmailRequestedEvent::class => [
        // Confirmation Email
        UserConfirmationEmailHandler::class,
    ],
];
