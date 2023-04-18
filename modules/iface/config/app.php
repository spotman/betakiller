<?php

return [
    'url' => [
        'base' => getenv('APP_URL'),

        'is_trailing_slash_enabled' => false,
        'circular_link_href'        => '#',
    ],

    'cache' => [
        'page' => [
            'enabled' => false,
            'path'    => 'cache'.DIRECTORY_SEPARATOR.'page',
        ],
    ],
];
