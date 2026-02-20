<?php

use BetaKiller\DI\ContainerInterface;
use BetaKiller\Factory\UrlElementFactory;
use BetaKiller\Factory\UrlElementFactoryInterface;
use BetaKiller\IFace\Cache\DummyIFaceCache;
use BetaKiller\IFace\Cache\IFaceCacheInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlDispatcherCacheWrapper;
use BetaKiller\Url\UrlDispatcherInterface;
use BetaKiller\Url\UrlElementRenderer;
use BetaKiller\Url\UrlElementRendererInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeLazyProxy;
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

        UrlElementFactoryInterface::class => autowire(UrlElementFactory::class),

        UrlElementTreeValidatorInterface::class => autowire(UrlElementTreeValidator::class),

        UrlElementTreeInterface::class => DI\autowire(UrlElementTreeLazyProxy::class),

        UrlElementRendererInterface::class => autowire(UrlElementRenderer::class), /* ->lazy() */

        WidgetFacade::class => autowire(WidgetFacade::class),

        IFaceRendererInterface::class => autowire(DefaultIFaceRenderer::class),
        IFaceCacheInterface::class    => autowire(DummyIFaceCache::class),
    ],

];
