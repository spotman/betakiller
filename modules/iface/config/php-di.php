<?php

use BetaKiller\DI\ContainerInterface;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlDispatcherCacheWrapper;
use BetaKiller\Url\UrlDispatcherInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeLoader;

return [

    'definitions' => [

        UrlContainerInterface::class => DI\autowire(ResolvingUrlContainer::class),

        UrlDispatcherInterface::class => DI\factory(function (UrlDispatcher $proxy, ContainerInterface $container) {
            return $container->make(UrlDispatcherCacheWrapper::class, [
                'proxy' => $proxy,
            ]);
        }),

        UrlElementTreeInterface::class => DI\factory(function (UrlElementTreeLoader $loader) {
            return $loader->load();
        }),
    ],

];
