<?php
declare(strict_types=1);

use BetaKiller\Model\RoleInterface;

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
            RoleInterface::DEVELOPER,
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
        \BetaKiller\Task\Error\Notify::NOTIFICATION_PHP_EXCEPTION => [
            'group' => ERROR_MANAGEMENT_GROUP,
        ],

        \BetaKiller\Error\PhpExceptionStorageHandler::NOTIFICATION_SUBSYSTEM_FAILURE => [
            'group' => ERROR_MANAGEMENT_GROUP,
        ],
    ],
];
