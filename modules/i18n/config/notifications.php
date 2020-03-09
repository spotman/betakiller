<?php
declare(strict_types=1);

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Notification\Transport\EmailTransport;
use BetaKiller\Task\Import\I18n;

define('TRANSLATION_MANAGEMENT_GROUP', 'i18n-management');

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
        TRANSLATION_MANAGEMENT_GROUP => [
            'roles'     => [
                I18nFacade::ROLE_TRANSLATOR,
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
        I18n::NOTIFICATION_NEW_KEYS => [
            'group'     => TRANSLATION_MANAGEMENT_GROUP,
            'transport' => EmailTransport::CODENAME,
            'broadcast' => true,
        ],
    ],
];
