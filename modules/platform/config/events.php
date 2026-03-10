<?php

declare(strict_types=1);

use BetaKiller\Event\UserActivatedEvent;
use BetaKiller\Event\UserApprovedEvent;
use BetaKiller\Event\UserBannedEvent;
use BetaKiller\Event\UserConfirmationEmailRequestedEvent;
use BetaKiller\Event\UserCreatedEvent;
use BetaKiller\Event\UserDeactivatedEvent;
use BetaKiller\Event\UserEmailChangedEvent;
use BetaKiller\Event\UserPasswordChangedEvent;
use BetaKiller\Event\UserPasswordChangeRequestedEvent;
use BetaKiller\Event\UserPendingEvent;
use BetaKiller\Event\UserRejectedEvent;
use BetaKiller\Event\UserResumedEvent;
use BetaKiller\Event\UserSuspendedEvent;
use BetaKiller\Event\UserUnbannedEvent;
use BetaKiller\EventHandler\UserApprovedAutoActivate;
use BetaKiller\EventHandler\UserConfirmationEmailHandler;
use BetaKiller\EventHandler\UserCreatedAutoRequestCheck;
use BetaKiller\EventHandler\UserPasswordChangedClearTokensHandler;
use BetaKiller\EventHandler\UserPasswordChangeRequestedEmailHandler;
use BetaKiller\EventHandler\UserPendingAutoApprove;
use BetaKiller\EventHandler\UserUnbannedAutoActivate;

return [
    UserCreatedEvent::class => [
        UserCreatedAutoRequestCheck::class,
    ],

    UserPendingEvent::class => [
        UserPendingAutoApprove::class,
    ],

    UserApprovedEvent::class => [
        UserApprovedAutoActivate::class,
    ],

    UserRejectedEvent::class => [
        // Bind handlers here if needed
    ],

    UserActivatedEvent::class => [
        // Bind handlers here if needed
    ],

    UserDeactivatedEvent::class => [
        // Bind handlers here if needed
    ],

    UserBannedEvent::class => [
        // Bind handlers here if needed
    ],

    UserUnbannedEvent::class => [
        UserUnbannedAutoActivate::class,
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
