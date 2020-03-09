<?php
declare(strict_types=1);

use BetaKiller\Model\RoleInterface;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Task\Test\Notification\SendBroadcast;
use BetaKiller\Task\Test\Notification\SendDirect;

define('TEST_NOTIFICATIONS_GROUP', 'test-notifications');

return [
    'transports' => [
        EmailTransport::CODENAME,
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
    'groups'     => [
        TEST_NOTIFICATIONS_GROUP => [
            'roles' => [
                RoleInterface::DEVELOPER,
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
    'messages'   => [
        SendDirect::NOTIFICATION_TEST_DIRECT => [
            'group'     => TEST_NOTIFICATIONS_GROUP,
            'transport' => EmailTransport::CODENAME,
            'critical'  => true,
        ],

        SendBroadcast::NOTIFICATION_TEST_BROADCAST => [
            'group'     => TEST_NOTIFICATIONS_GROUP,
            'transport' => EmailTransport::CODENAME,
            'broadcast' => true,
        ],
    ],
];
