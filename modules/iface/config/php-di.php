<?php

use BetaKiller\DI\ContainerInterface;
use BetaKiller\IFace\Cache\DummyIFaceCache;
use BetaKiller\IFace\Cache\IFaceCacheInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlDispatcherCacheWrapper;
use BetaKiller\Url\UrlDispatcherInterface;
use BetaKiller\Url\UrlElementRenderer;
use BetaKiller\Url\UrlElementRendererInterface;
use BetaKiller\Url\UrlElementTreeFactory;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeValidator;
use BetaKiller\Url\UrlElementTreeValidatorInterface;
use BetaKiller\View\DefaultIFaceRenderer;
use BetaKiller\View\IFaceRendererInterface;
use BetaKiller\Widget\WidgetFacade;

use function DI\autowire;

return [

    'definitions' => [
        UrlDispatcherInterface::class => DI\factory(static function (
            UrlDispatcher $proxy,
            ContainerInterface $container
        ) {
            return $container->make(UrlDispatcherCacheWrapper::class, [
                'proxy' => $proxy,
            ]);
        }),

        UrlElementTreeValidatorInterface::class => autowire(UrlElementTreeValidator::class),

        UrlElementTreeInterface::class => DI\factory(static function (UrlElementTreeFactory $factory) {
            return $factory();
        }),

        UrlElementRendererInterface::class => autowire(UrlElementRenderer::class), /* ->lazy() */

        WidgetFacade::class => autowire(WidgetFacade::class),

        IFaceRendererInterface::class => autowire(DefaultIFaceRenderer::class),
        IFaceCacheInterface::class    => autowire(DummyIFaceCache::class),
    ],

];
