<?php defined('SYSPATH') or die('No direct script access.');

return [
    'default' => [
        // No caching in dev mode
        'adapter' => 'Array',
        'expire'  => 3600,
    ],
];
