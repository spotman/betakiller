<?php

use BetaKiller\Config\WampConfig;

return [
    'realms' => [
        WampConfig::CONFIG_REALM_KEY_EXT => 'public',
        WampConfig::CONFIG_REALM_KEY_INT => 'internal',
    ],

    'client' => [
        'host' => \getenv('WAMP_CLIENT_HOST'),
        'port' => \getenv('WAMP_CLIENT_PORT'),
    ],

    'server' => [
        'host' => \getenv('WAMP_SERVER_HOST'),
        'port' => \getenv('WAMP_SERVER_PORT'),
    ],
];
