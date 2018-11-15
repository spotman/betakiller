<?php
declare(strict_types=1);

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
            \BetaKiller\I18n\I18nFacade::ROLE_TRANSLATOR,
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
        \BetaKiller\Task\Import\I18n::NOTIFICATION_NEW_KEYS => [
            'group' => TRANSLATION_MANAGEMENT_GROUP,
        ],
    ],
];
