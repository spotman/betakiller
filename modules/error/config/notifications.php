<?php

declare(strict_types=1);

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Notification\Message\DeveloperErrorPhpExceptionMessage;
use BetaKiller\Notification\Transport\EmailTransport;

const ERROR_MANAGEMENT_GROUP = 'error-management';

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
    NotificationConfig::ROOT_GROUPS   => [
        ERROR_MANAGEMENT_GROUP => [
            NotificationConfig::ROLES => [
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
    NotificationConfig::ROOT_MESSAGES => [
        DeveloperErrorPhpExceptionMessage::getCodename() => [
            NotificationConfig::FQCN      => DeveloperErrorPhpExceptionMessage::class,
            NotificationConfig::GROUP     => ERROR_MANAGEMENT_GROUP,
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
        ],
    ],
];
