<?php

declare(strict_types=1);

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Task\Test\Notification\SendBroadcast;
use BetaKiller\Task\Test\Notification\SendDirect;

return [
    NotificationConfig::ROOT_TRANSPORTS => [
        EmailTransport::getName(),
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
    NotificationConfig::ROOT_GROUPS     => [
        NotificationHelper::TEST_NOTIFICATIONS_GROUP => [
            NotificationConfig::ROLES => [
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
    NotificationConfig::ROOT_MESSAGES   => [
        SendDirect::NOTIFICATION_TEST_DIRECT => [
            NotificationConfig::GROUP     => NotificationHelper::TEST_NOTIFICATIONS_GROUP,
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
            NotificationConfig::CRITICAL  => true,
        ],

        SendBroadcast::NOTIFICATION_TEST_BROADCAST => [
            NotificationConfig::GROUP     => NotificationHelper::TEST_NOTIFICATIONS_GROUP,
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
            NotificationConfig::BROADCAST => true,
        ],
    ],
];
