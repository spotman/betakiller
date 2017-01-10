<?php

return [
    'url'   =>  [
        'is_trailing_slash_enabled' =>  FALSE,
        'circular_link_href' =>  'javascript:void(0)',
    ],

    'cache' =>  [
        'enabled'   =>  Kohana::$caching,

        'page' =>  [
            'path'  =>  APPPATH.'page-cache'
        ]
    ],
];
