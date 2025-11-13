<?php
declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\Middleware\ErrorPageMiddleware;
use BetaKiller\Middleware\ExpectedExceptionMiddleware;
use BetaKiller\Middleware\I18nMiddleware;
use BetaKiller\Middleware\UserMiddleware;

return [
    WebConfig::KEY_MIDDLEWARES => [
        // Exceptions handling (depends on i18n)
        ErrorPageMiddleware::class => [
            I18nMiddleware::class,
            UserMiddleware::class,
        ],

        ExpectedExceptionMiddleware::class => [
            ErrorPageMiddleware::class,
        ],
    ],

    WebConfig::KEY_PIPE => [
        ErrorPageMiddleware::class,
        ExpectedExceptionMiddleware::class,
    ],
];
