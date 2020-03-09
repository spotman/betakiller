<?php
declare(strict_types=1);

use BetaKiller\Model\RoleInterface;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Task\HitStat\ProcessHits;

define('HIT_STAT_GROUP', 'hit-stat');

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
        HIT_STAT_GROUP => [
            'roles'     => [
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
    'messages' => [
        ProcessHits::MISSING_TARGETS => [
            'group'     => HIT_STAT_GROUP,
            'transport' => EmailTransport::CODENAME,
            'broadcast' => true,
        ],

        ProcessHits::NEW_SOURCES => [
            'group'     => HIT_STAT_GROUP,
            'transport' => EmailTransport::CODENAME,
            'broadcast' => true,
        ],
    ],
];
