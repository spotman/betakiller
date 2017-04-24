<?php

use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\IFace\Url\UrlParameters;
use BetaKiller\IFace\Url\UrlParametersInterface;

return [

    'definitions' => [

        UrlDispatcher::class => DI\object(UrlDispatcher::class)->scope(\DI\Scope::SINGLETON),

        UrlParametersInterface::class => DI\object(UrlParameters::class),

        IFaceCache::class => DI\object(IFaceCache::class)->scope(\DI\Scope::PROTOTYPE),

    ],

];
