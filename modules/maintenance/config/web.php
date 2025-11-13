<?php

declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\Middleware\ErrorPageMiddleware;
use BetaKiller\Middleware\MaintenanceModeMiddleware;

return [
    WebConfig::KEY_MIDDLEWARES => [
        MaintenanceModeMiddleware::class => [
            // Throws raw 501 exception, proceeded by ErrorPageMiddleware
            ErrorPageMiddleware::class,
        ],
    ],

    WebConfig::KEY_PIPE => [
        MaintenanceModeMiddleware::class,
    ],
];
