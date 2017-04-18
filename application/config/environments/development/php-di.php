<?php

use BetaKiller\Factory\CommonFactoryCache;
use BetaKiller\Factory\FactoryCacheInterface;
use DI\Scope;
use Doctrine\Common\Cache\ArrayCache;
use Spotman\Acl\Acl;

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache' => new ArrayCache(),

    'definitions' => [

        // Basic caching for request lifetime
        FactoryCacheInterface::class => DI\factory(function () {
            return new CommonFactoryCache();
        })->scope(Scope::SINGLETON),

        Acl::DI_CACHE_OBJECT_KEY => DI\object(ArrayCache::class),

    ],

];
