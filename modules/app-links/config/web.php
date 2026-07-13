<?php

declare(strict_types=1);

use BetaKiller\AppLinks\AppLinksWellKnownHandler;
use BetaKiller\Config\WebConfig;

return [
    WebConfig::KEY_ROUTES => [
        WebConfig::KEY_GET => [
            '/.well-known/[{file:.*}]' => AppLinksWellKnownHandler::class,
        ],
    ],
];
