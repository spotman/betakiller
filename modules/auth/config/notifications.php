<?php
declare(strict_types=1);

use BetaKiller\Action\Auth\ConfirmEmailAction;
use BetaKiller\Action\Auth\VerifyAccessRecoveryTokenAction;
use BetaKiller\Action\Auth\VerifyPasswordChangeTokenAction;
use BetaKiller\EventHandler\UserConfirmationEmailHandler;
use BetaKiller\EventHandler\UserPasswordChangeRequestedEmailHandler;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Service\AccessRecoveryService;

define('AUTH_USER_GROUP', 'auth-user');

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
    'groups'   => [
        AUTH_USER_GROUP => [
            'is_system' => true,
            'roles'     => [
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
    'messages' => [
        AccessRecoveryService::NOTIFICATION_NAME => [
            'group'     => AUTH_USER_GROUP,
            'action'    => VerifyAccessRecoveryTokenAction::codename(),
            'transport' => EmailTransport::getName(),
        ],

        UserConfirmationEmailHandler::EMAIL_VERIFICATION => [
            'group'     => AUTH_USER_GROUP,
            'action'    => ConfirmEmailAction::codename(),
            'transport' => EmailTransport::getName(),
        ],

        UserPasswordChangeRequestedEmailHandler::REQUEST_PASSWORD_CHANGE => [
            'group'     => AUTH_USER_GROUP,
            'action'    => VerifyPasswordChangeTokenAction::codename(),
            'transport' => EmailTransport::getName(),
        ],
    ],
];
