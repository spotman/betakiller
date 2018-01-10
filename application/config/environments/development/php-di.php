<?php

use BetaKiller\Factory\CommonFactoryCache;
use BetaKiller\Factory\FactoryCacheInterface;
use BetaKiller\Url\UrlDispatcherCache;
use BetaKiller\Url\UrlDispatcherCacheInterface;
use DI\Scope;
use Doctrine\Common\Cache\ArrayCache;
use Spotman\Acl\AclInterface;

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

        // Cache for url dispatcher
        UrlDispatcherCacheInterface::class => DI\factory(function () {
            return new UrlDispatcherCache();
        })->scope(Scope::SINGLETON),

        AclInterface::DI_CACHE_OBJECT_KEY => DI\object(ArrayCache::class),

    ],

];
