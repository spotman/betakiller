<?php
declare(strict_types=1);

use BetaKiller\Config\WebConfig;
use BetaKiller\RobotsTxt\RobotsTxtHandler;

return [
    WebConfig::KEY_ROUTES => [
        WebConfig::KEY_GET => [
            '/robots.txt' => RobotsTxtHandler::class,
        ],
    ],
];
