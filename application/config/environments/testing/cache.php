<?php defined('SYSPATH') or die('No direct script access.');

return [
    'default' => [
        // No caching in testing mode
        'adapter' => 'Array',
        'expire'  => 3600,
    ],
];
