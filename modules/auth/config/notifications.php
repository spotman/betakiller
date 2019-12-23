<?php
declare(strict_types=1);

use BetaKiller\Action\Auth\ConfirmEmailAction;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Service\AccessRecoveryService;
use BetaKiller\Service\AuthService;
use BetaKiller\Workflow\UserWorkflow;

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
            'group' => AUTH_USER_GROUP,
        ],

        UserWorkflow::NOTIFICATION_EMAIL_VERIFICATION => [
            'group' => AUTH_USER_GROUP,
            'action' => ConfirmEmailAction::codename(),
        ],

        AuthService::REQUEST_PASSWORD_CHANGE => [
            'group' => AUTH_USER_GROUP,
        ],
    ],
];
