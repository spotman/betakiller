<?php

declare(strict_types=1);

use BetaKiller\Action\Auth\ConfirmEmailAction;
use BetaKiller\Action\Auth\VerifyAccessRecoveryTokenAction;
use BetaKiller\Action\Auth\VerifyPasswordChangeTokenAction;
use BetaKiller\Config\NotificationConfig;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Notification\Message\UserAccessRecoveryMessage;
use BetaKiller\Notification\Message\UserPasswordChangeRequestMessage;
use BetaKiller\Notification\Message\UserVerificationMessage;
use BetaKiller\Notification\Transport\EmailTransport;

const AUTH_USER_GROUP = 'auth-user';

return [
    /**
     * Notification groups and relation to ACL roles
     *
     * [
     *   groupCodename1:[roleCodename1,roleCodename2,..],
     *   groupCodename2:[..],
     *   ..
     * ]
     */
    NotificationConfig::ROOT_GROUPS   => [
        AUTH_USER_GROUP => [
            NotificationConfig::IS_SYSTEM => true,
            NotificationConfig::ROLES     => [
                // Target is specified per message
                RoleInterface::LOGIN,
            ],
        ],
    ],

    /**
     * Messages options
     *
     * [
     *   messageCodename1:[
     *     'group':groupCodename,
     *   ],
     *   messageCodename2:[..],
     * ]
     */
    NotificationConfig::ROOT_MESSAGES => [
        UserAccessRecoveryMessage::getCodename() => [
            NotificationConfig::FQCN      => UserAccessRecoveryMessage::class,
            NotificationConfig::GROUP     => AUTH_USER_GROUP,
            NotificationConfig::ACTION    => VerifyAccessRecoveryTokenAction::codename(),
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
        ],

        UserVerificationMessage::getCodename() => [
            NotificationConfig::FQCN      => UserVerificationMessage::class,
            NotificationConfig::GROUP     => AUTH_USER_GROUP,
            NotificationConfig::ACTION    => ConfirmEmailAction::codename(),
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
        ],

        UserPasswordChangeRequestMessage::getCodename() => [
            NotificationConfig::FQCN      => UserPasswordChangeRequestMessage::class,
            NotificationConfig::GROUP     => AUTH_USER_GROUP,
            NotificationConfig::ACTION    => VerifyPasswordChangeTokenAction::codename(),
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
        ],
    ],
];
