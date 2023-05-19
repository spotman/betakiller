<?php
declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\Middleware\SitemapRequestHandler;

return [
    WebConfig::KEY_ROUTES => [
        WebConfig::KEY_GET => [
            '/sitemap.xml' => SitemapRequestHandler::class,
        ],
    ],
];
