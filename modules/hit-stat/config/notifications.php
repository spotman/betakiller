<?php
declare(strict_types=1);

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
        \BetaKiller\Task\HitStat\ProcessHits::MISSING_TARGETS => [
            'group' => HIT_STAT_GROUP,
        ],

        \BetaKiller\Task\HitStat\ProcessHits::NEW_SOURCES => [
            'group' => HIT_STAT_GROUP,
        ],
    ],
];
