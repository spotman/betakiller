<?php

use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\Url\ResolvingUrlContainer;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;

return [

    'definitions' => [

        UrlContainerInterface::class => DI\object(ResolvingUrlContainer::class),
        UrlDispatcher::class         => DI\object(UrlDispatcher::class)->scope(\DI\Scope::SINGLETON),
        IFaceCache::class            => DI\object(IFaceCache::class)->scope(\DI\Scope::PROTOTYPE),

    ],

];
