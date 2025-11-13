<?php

declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\HitStat\HitStatMiddleware;
use BetaKiller\Middleware\UrlHelperMiddleware;

return [
    WebConfig::KEY_MIDDLEWARES => [
        // Save stat (referrer, target, utm markers, etc) (depends on UrlContainer)
        HitStatMiddleware::class => [
            UrlHelperMiddleware::class,
        ],
    ],
];
