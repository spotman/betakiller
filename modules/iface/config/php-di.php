<?php

use BetaKiller\DI\ContainerInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlDispatcherCacheWrapper;
use BetaKiller\Url\UrlDispatcherInterface;
use BetaKiller\Url\UrlElementRenderer;
use BetaKiller\Url\UrlElementRendererInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeLoader;
use BetaKiller\Widget\WidgetFacade;
use function DI\autowire;

return [

    'definitions' => [
        UrlDispatcherInterface::class => DI\factory(static function (
            UrlDispatcher      $proxy,
            ContainerInterface $container
        ) {
            return $container->make(UrlDispatcherCacheWrapper::class, [
                'proxy' => $proxy,
            ]);
        }),

        UrlElementTreeInterface::class => DI\factory(static function (UrlElementTreeLoader $loader) {
            return $loader->load();
        }),

        UrlElementRendererInterface::class => autowire(UrlElementRenderer::class), /* ->lazy() */

        WidgetFacade::class => autowire(WidgetFacade::class),
    ],

];
