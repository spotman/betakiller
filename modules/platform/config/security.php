<?php
declare(strict_types=1);

use BetaKiller\Session\SessionStorage;

return [
    'csp' => [
        'enabled'   => false,
        'report'    => false,
        'safe_mode' => false,
        'errors'    => false,
        'rules'     => [],
    ],

    'hsts' => [
        'enabled'    => true,
        'max_age'    => 31536000,
        'subdomains' => false,
        'preload'    => false,
    ],

    'cookies' => [
        'protected' => [
            // This cookie is used by WAMP JS client as auth id
            SessionStorage::COOKIE_NAME,
        ],
    ],
];
