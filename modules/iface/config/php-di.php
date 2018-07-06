<?php

use BetaKiller\Url\ResolvingUrlContainer;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeLoader;

return [

    'definitions' => [

        UrlContainerInterface::class => DI\object(ResolvingUrlContainer::class),

        UrlElementTreeInterface::class => DI\factory(function(UrlElementTreeLoader $loader) {
            return $loader->load();
        }),
    ],

];
