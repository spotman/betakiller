<?php

declare(strict_types=1);

use BetaKiller\Config\NotificationConfig;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Notification\Message\TranslatorI18nNewKeysMessage;
use BetaKiller\Notification\Transport\EmailTransport;

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
            'roles' => [
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
        TranslatorI18nNewKeysMessage::getCodename() => [
            NotificationConfig::FQCN      => TranslatorI18nNewKeysMessage::class,
            NotificationConfig::GROUP     => TRANSLATION_MANAGEMENT_GROUP,
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
        ],
    ],
];
