<?php
declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\Middleware\I18nMiddleware;
use BetaKiller\Middleware\ProfilerMiddleware;
use BetaKiller\RequestHandler\App\I18next\AddMissingTranslationRequestHandler;
use BetaKiller\RequestHandler\App\I18next\FetchTranslationRequestHandler;

return [
    WebConfig::KEY_MIDDLEWARES => [
        I18nMiddleware::class => [],
    ],

    WebConfig::KEY_PIPE => [
        I18nMiddleware::class,
    ],

    WebConfig::KEY_ROUTES => [
        WebConfig::KEY_GET => [
            '/i18n/{lang}' => FetchTranslationRequestHandler::class,
        ],

        WebConfig::KEY_POST => [
            '/i18n/{lang}/add-missing' => AddMissingTranslationRequestHandler::class,
        ],
    ],
];
