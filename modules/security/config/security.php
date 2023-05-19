<?php
declare(strict_types=1);

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

    'headers' => [
        'add'    => [
            // Allow nested iframes from the same domain
            'X-Frame-Options' => 'SAMEORIGIN',
        ],
        'remove' => [],
    ],
];
