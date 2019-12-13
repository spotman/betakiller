<?php
declare(strict_types=1);

use BetaKiller\Notification\Transport\EmailTransport;

define('TEST_NOTIFICATIONS_GROUP', 'test-notifications');

return [
    'transports' => [
        // Lowest priority by default
        EmailTransport::CODENAME => 1000,
    ],

    /**
     * Notification groups and relation to ACL roles
     *
     * [
     *   groupCodename1 => [role_codename1, role_codename2, ...],
     *   groupCodename2 => [...],
     *   ...
     * ]
     */
    'groups'   => [
        TEST_NOTIFICATIONS_GROUP => [
            'roles' => [
                \BetaKiller\Model\RoleInterface::DEVELOPER,
            ],
        ],
    ],

    /**
     * Messages options
     *
     * [
     *   messageCodename1 => [
     *     'group' => groupCodename,
     *   ],
     *   messageCodename2 => [...],
     *   ...
     * ]
     */
    'messages' => [
        \BetaKiller\Task\Test\Notification\Send::NOTIFICATION_TEST => [
            'group' => TEST_NOTIFICATIONS_GROUP,
        ],
    ],
];
