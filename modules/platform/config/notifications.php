<?php
declare(strict_types=1);

use BetaKiller\Model\RoleInterface;
use BetaKiller\Service\AbstractRecoveryAccessService;
use BetaKiller\Service\AbstractUserVerificationService;

define('RECOVERY_ACCESS_GROUP', 'recovery-access-user');
define('VERIFICATION_EMAIL_GROUP', 'verification-user');

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
        RECOVERY_ACCESS_GROUP => [
            'is_system' => true,
            'roles'     => [
                // Target is specified per message
                RoleInterface::LOGIN,
            ],
        ],

        VERIFICATION_EMAIL_GROUP => [
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
        AbstractRecoveryAccessService::NOTIFICATION_NAME => [
            'group' => RECOVERY_ACCESS_GROUP,
        ],

        AbstractUserVerificationService::NOTIFICATION_NAME => [
            'group' => VERIFICATION_EMAIL_GROUP,
        ],
    ],
];
