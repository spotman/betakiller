<?php

declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\Middleware\CustomErrorPageMiddleware;
use BetaKiller\Middleware\DebugMiddleware;
use BetaKiller\Middleware\I18nMiddleware;
use BetaKiller\Middleware\SessionMiddleware;
use BetaKiller\Middleware\UrlElementCheckMiddleware;
use BetaKiller\Middleware\UrlElementDispatchMiddleware;
use BetaKiller\Middleware\UrlElementRenderMiddleware;
use BetaKiller\Middleware\UrlHelperMiddleware;
use BetaKiller\Middleware\UserLanguageMiddleware;
use BetaKiller\Middleware\UserMiddleware;
use BetaKiller\Middleware\UserStatusMiddleware;
use Mezzio\Flash\FlashMessageMiddleware;

return [
    WebConfig::KEY_MIDDLEWARES       => [

        // Debugging (depends on session, user and profiler)
        DebugMiddleware::class => [
            SessionMiddleware::class,
            UserMiddleware::class,
        ],

        UserLanguageMiddleware::class     => [
            I18nMiddleware::class,
            UserMiddleware::class,
        ],

        // Flash messages for Post-Redirect-Get flow (requires Session)
        FlashMessageMiddleware::class     => [
            SessionMiddleware::class,
        ],

//        UrlHelperMiddleware::class          => [
//            DispatchMiddleware::class,
//        ],
//
//        // Display custom 404 page for dispatched UrlElement
//        CustomNotFoundPageMiddleware::class => [
//            UrlHelperMiddleware::class,
//            class_exists(HitStatMiddleware::class) ? HitStatMiddleware::class : DummyMiddleware::class,
//        ],
//
//        // Dispatch UrlElement
//        UrlElementDispatchMiddleware::class => [
//            UrlHelperMiddleware::class,
//            CustomNotFoundPageMiddleware::class,
//        ],
//
//        // Render UrlElement
//        UrlElementRenderMiddleware::class   => [
//            UrlElementDispatchMiddleware::class,
//            UserStatusMiddleware::class,
//        ],

        // UrlElement processing
        UrlElementRenderMiddleware::class => [
            // Heavy operation
            UrlHelperMiddleware::class,

            // Display custom 404 page for dispatched UrlElement
            CustomErrorPageMiddleware::class,

            // Depends on UrlHelper
            UrlElementDispatchMiddleware::class,

            // Depends on dispatched URL
            UrlElementCheckMiddleware::class,
        ],

        // Prevent access for locked users
        UserStatusMiddleware::class       => [
            UserMiddleware::class,
            UrlElementDispatchMiddleware::class,
        ],
    ],

    // Common pipe
    WebConfig::KEY_PIPE              => [
        DebugMiddleware::class,
        UserLanguageMiddleware::class,
        FlashMessageMiddleware::class,
    ],

    // Render UrlElement
    WebConfig::KEY_NOT_FOUND_HANDLER => UrlElementRenderMiddleware::class,

];
