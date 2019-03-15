<?php

use BetaKiller\Config\WampConfig;

return [
    'realms' => [
        WampConfig::CONFIG_REALM_KEY_EXT => 'public',
        WampConfig::CONFIG_REALM_KEY_INT => 'internal',
    ],

    'connection' => [
        'host' => \getenv('WAMP_HOST'),
        'port' => \getenv('WAMP_PORT'),
    ],
];
