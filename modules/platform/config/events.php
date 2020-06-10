<?php
declare(strict_types=1);

use BetaKiller\Event\UserBlockedEvent;
use BetaKiller\Event\UserConfirmationEmailRequestedEvent;
use BetaKiller\Event\UserEmailChangedEvent;
use BetaKiller\Event\UserPasswordChangedEvent;
use BetaKiller\Event\UserPasswordChangeRequestedEvent;
use BetaKiller\Event\UserResumedEvent;
use BetaKiller\Event\UserSuspendedEvent;
use BetaKiller\Event\UserUnlockedEvent;
use BetaKiller\EventHandler\UserConfirmationEmailHandler;
use BetaKiller\EventHandler\UserPasswordChangedClearTokensHandler;
use BetaKiller\EventHandler\UserPasswordChangeRequestedEmailHandler;

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

    UserPasswordChangeRequestedEvent::class => [
        // Confirmation Email
        UserPasswordChangeRequestedEmailHandler::class,
    ],

    UserPasswordChangedEvent::class => [
        // Clear all tokens for security purpose
        UserPasswordChangedClearTokensHandler::class,
    ],
];
