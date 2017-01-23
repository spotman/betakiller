<?php

return [
    'url'   =>  [
        'is_trailing_slash_enabled' =>  FALSE,
        'circular_link_href' =>  'javascript:void(0)',
    ],

    'cache' =>  [
        'page' =>  [
            'enabled'   =>  Kohana::$caching,
            'path'      =>  APPPATH.'cache'.DIRECTORY_SEPARATOR.'page'
        ]
    ],
];
