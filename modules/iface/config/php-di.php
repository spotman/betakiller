<?php

use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\IFace\Url\UrlContainer;
use BetaKiller\IFace\Url\UrlContainerInterface;

return [

    'definitions' => [

        UrlDispatcher::class => DI\object(UrlDispatcher::class)->scope(\DI\Scope::SINGLETON),

        UrlContainerInterface::class => DI\object(UrlContainer::class),

        IFaceCache::class => DI\object(IFaceCache::class)->scope(\DI\Scope::PROTOTYPE),

    ],

];
