<?php
declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\Security\CspReportHandler;
use BetaKiller\Security\SecureHeadersMiddleware;

return [
    WebConfig::KEY_MIDDLEWARES => [
        SecureHeadersMiddleware::class => [],
    ],

    WebConfig::KEY_PIPE => [
        SecureHeadersMiddleware::class,
    ],

    WebConfig::KEY_ROUTES => [
        WebConfig::KEY_POST => [
            CspReportHandler::URL => CspReportHandler::class,
        ],
    ],

];
