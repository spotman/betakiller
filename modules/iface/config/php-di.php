<?php

use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\Url\ResolvingUrlContainer;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\IFace\Url\UrlDispatcher;

return [

    'definitions' => [

        UrlContainerInterface::class => DI\object(ResolvingUrlContainer::class),
        UrlDispatcher::class         => DI\object(UrlDispatcher::class)->scope(\DI\Scope::SINGLETON),
        IFaceCache::class            => DI\object(IFaceCache::class)->scope(\DI\Scope::PROTOTYPE),

    ],

];
