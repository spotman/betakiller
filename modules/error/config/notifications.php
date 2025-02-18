<?php
declare(strict_types=1);

use BetaKiller\Model\RoleInterface;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Task\Error\Notify;

define('ERROR_MANAGEMENT_GROUP', 'error-management');

return [
    /**
     * Notification groups and relation to ACL roles
     *
     * [
     *   groupCodename1:[role_codename1,role_codename2,..],
     *   groupCodename2:[..],
     *   ..
     * ]
     */
    'groups'   => [
        ERROR_MANAGEMENT_GROUP => [
            'roles' => [
                RoleInterface::DEVELOPER,
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
        Notify::NOTIFICATION_PHP_EXCEPTION => [
            'group'     => ERROR_MANAGEMENT_GROUP,
            'transport' => EmailTransport::getName(),
            'critical'  => true,
        ],
    ],
];
