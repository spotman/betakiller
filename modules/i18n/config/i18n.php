<?php
declare(strict_types=1);

return [
    'loaders' => [
        // Standard i18n from database
//        \BetaKiller\I18n\DatabaseI18nLoader::class,
        \BetaKiller\I18n\FilesystemI18nKeysLoader::class,
    ],
];
