<?php
declare(strict_types=1);

use BetaKiller\I18n\FilesystemI18nKeysLoader;
use BetaKiller\I18n\I18nConfig;

return [
    I18nConfig::KEY_LOADERS => [
        // Standard i18n from database
//        \BetaKiller\I18n\DatabaseI18nLoader::class,
        FilesystemI18nKeysLoader::class,
    ],
];
