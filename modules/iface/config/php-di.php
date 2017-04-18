<?php

use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\IFace\Url\UrlParameters;

return [

    'definitions'       =>  [

        UrlDispatcher::class =>  DI\object(UrlDispatcher::class)->scope(\DI\Scope::SINGLETON),

        UrlParameters::class =>  DI\factory(function() {
            return UrlParameters::create();
        })->scope(\DI\Scope::PROTOTYPE),

        IFaceCache::class  =>  DI\object(IFaceCache::class)->scope(\DI\Scope::PROTOTYPE),

    ],

];
