<?php
declare(strict_types=1);

return [
    'csp' => [
        'enabled' => true,
        'safe_mode' => false,
        'rules' => [],
    ],
    'headers' => [
        'add' => [
            // Allow nested iframes from the same domain
            'X-Frame-Options' => 'SAMEORIGIN',
        ],
        'remove' => [],
    ],
];
