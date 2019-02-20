<?php
declare(strict_types=1);

define('TEST_NOTIFICATIONS_GROUP', 'test-notifications');

return [
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
