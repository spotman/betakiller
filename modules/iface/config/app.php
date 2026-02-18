<?php

use BetaKiller\Url\Parameter\ID;
use BetaKiller\Url\Parameter\Page;
use BetaKiller\Url\Parameter\UtmCampaign;
use BetaKiller\Url\Parameter\UtmContent;
use BetaKiller\Url\Parameter\UtmMedium;
use BetaKiller\Url\Parameter\UtmSource;
use BetaKiller\Url\Parameter\UtmTerm;

return [
    'url' => [
        'base' => getenv('APP_URL'),

        'is_trailing_slash_enabled'   => false,
        'is_redirect_missing_enabled' => false,
        'circular_link_href'          => '#',

        'parameters' => [
            // Common params
            ID::class,
            Page::class,

            // UTM markers
            UtmCampaign::class,
            UtmContent::class,
            UtmMedium::class,
            UtmSource::class,
            UtmTerm::class,
        ],
    ],

    'cache' => [
        'page' => [
            'enabled' => false,
            'path'    => 'cache'.DIRECTORY_SEPARATOR.'page',
        ],
    ],
];
