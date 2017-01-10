<?php

use BetaKiller\IFace\Cache\IFaceCache;

return [

    'definitions'       =>  [

        \URL_Dispatcher::class  =>  DI\object(\URL_Dispatcher::class)->scope(\DI\Scope::SINGLETON),

        \URL_Parameters::class  =>  DI\factory(function() {
            return \URL_Parameters::instance();
        })->scope(\DI\Scope::PROTOTYPE),

        IFaceCache::class  =>  DI\object(IFaceCache::class)->scope(\DI\Scope::PROTOTYPE),

    ],

];
