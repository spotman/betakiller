<?php
declare(strict_types=1);

return [
    'csp' => [
        'safe_mode' => true,
        'errors'    => true,

        'rules' => [
            'connect' => [
//                'http://localhost:63342',   // IDEA IDE link
                'https://localhost:63342',  // IDEA IDE link
            ],
        ],
    ],

    'hsts' => [
        'max_age'    => 60,
        'subdomains' => false,
        'preload'    => false,
    ],
];
