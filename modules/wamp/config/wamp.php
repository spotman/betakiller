<?php

return [
    'realmName'  => 'public',
    'connection' => [
        'host' => \getenv('WAMP_HOST'),
        'port' => \getenv('WAMP_PORT'),
    ],
];
