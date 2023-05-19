<?php
declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\HitStat\HitStatMiddleware;
use BetaKiller\Middleware\I18nMiddleware;
use BetaKiller\Middleware\ProfilerMiddleware;
use BetaKiller\Middleware\UrlHelperMiddleware;
use BetaKiller\RequestHandler\App\I18next\AddMissingTranslationRequestHandler;
use BetaKiller\RequestHandler\App\I18next\FetchTranslationRequestHandler;

return [
    WebConfig::KEY_MIDDLEWARES => [
        // Save stat (referrer, target, utm markers, etc) (depends on UrlContainer)
//        HitStatMiddleware::class => [
//            UrlHelperMiddleware::class,
//        ],
    ],
];
