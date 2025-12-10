<?php
declare(strict_types=1);

use BetaKiller\Event\UserApprovedEvent;
use BetaKiller\Event\UserBannedEvent;
use BetaKiller\Event\UserConfirmationEmailRequestedEvent;
use BetaKiller\Event\UserCreatedEvent;
use BetaKiller\Event\UserEmailChangedEvent;
use BetaKiller\Event\UserPasswordChangedEvent;
use BetaKiller\Event\UserPasswordChangeRequestedEvent;
use BetaKiller\Event\UserPendingEvent;
use BetaKiller\Event\UserResumedEvent;
use BetaKiller\Event\UserSuspendedEvent;
use BetaKiller\Event\UserUnbannedEvent;
use BetaKiller\EventHandler\UserConfirmationEmailHandler;
use BetaKiller\EventHandler\UserPasswordChangedClearTokensHandler;
use BetaKiller\EventHandler\UserPasswordChangeRequestedEmailHandler;

return [
    UserCreatedEvent::class => [
        // Bind handlers here if needed
    ],

    UserPendingEvent::class => [
        // Bind handlers here if needed
    ],

    UserApprovedEvent::class => [
        // Bind handlers here if needed
    ],

    UserBannedEvent::class => [
        // Bind handlers here if needed
    ],

    UserUnbannedEvent::class => [
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
