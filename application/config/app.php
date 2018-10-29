<?php

return [
    'url' => [
        'base' => getenv('APP_URL'),
    ],

    'cache' => [
        'page' => [
            'path' => 'cache'.DIRECTORY_SEPARATOR.'page',
        ],
    ],
];
