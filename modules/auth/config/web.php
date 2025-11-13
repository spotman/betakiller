<?php
declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\Middleware\SessionMiddleware;
use BetaKiller\Middleware\UserMiddleware;

return [
    WebConfig::KEY_MIDDLEWARES => [
        // Fetch Session
        SessionMiddleware::class => [],

        // Bind RequestUserProvider
        UserMiddleware::class    => [
            SessionMiddleware::class,
        ],

        // Prevent access for locked users
//        UserStatusMiddleware::class => [
//            UserMiddleware::class,
//            UrlHelperMiddleware::class,
//            UrlElementDispatchMiddleware::class,
//        ],
    ],

    WebConfig::KEY_PIPE => [
        // Fetch Session
        SessionMiddleware::class,

        // Bind RequestUserProvider
        UserMiddleware::class,
    ],
];
