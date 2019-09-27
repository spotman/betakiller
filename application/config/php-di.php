<?php

use BetaKiller\Acl\AclResourceFactory;
use BetaKiller\Acl\AclResourcesCollector;
use BetaKiller\Acl\AclRolesCollector;
use BetaKiller\Acl\AclRulesCollector;
use BetaKiller\Api\AccessResolver\CustomApiMethodAccessResolverDetector;
use BetaKiller\Assets\StaticAssets;
use BetaKiller\Config\AppConfig;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Event\EsbExternalEventTransport;
use BetaKiller\MessageBus\CommandBus;
use BetaKiller\MessageBus\CommandBusInterface;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\MessageBus\ExternalEventTransportInterface;
use BetaKiller\Notification\MessageRenderer;
use BetaKiller\Notification\MessageRendererInterface;
use BetaKiller\View\LayoutViewInterface;
use BetaKiller\View\TwigLayoutView;
use BetaKiller\View\TwigViewFactory;
use BetaKiller\View\ViewFactoryInterface;
use Doctrine\Common\Cache\Cache;
use Psr\SimpleCache\CacheInterface;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Spotman\Acl\ResourceFactory\AclResourceFactoryInterface;
use Spotman\Acl\ResourcesCollector\AclResourcesCollectorInterface;
use Spotman\Acl\RolesCollector\AclRolesCollectorInterface;
use Spotman\Acl\RulesCollector\AclRulesCollectorInterface;
use Spotman\Api\AccessResolver\ApiMethodAccessResolverDetectorInterface;

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'compile'           => true,

    // Enable this only if your server has APCu enabled
    'cache_definitions' => false,

    'annotations' => true,
    'autowiring'  => true,

    'definitions' => [

        // PSR-16 adapter for system-wide Doctrine Cache
        CacheInterface::class                       => DI\factory(function (Cache $doctrineCache) {
            return new SimpleCacheAdapter($doctrineCache);
        }),

        // Bind Doctrine cache interface to abstract cache provider
        Cache::class                                => DI\get(Doctrine\Common\Cache\CacheProvider::class),

        // Common cache instance for all
        \Doctrine\Common\Cache\CacheProvider::class => DI\get(\BetaKiller\Cache\DoctrineCacheProvider::class),

        AppConfigInterface::class                       => DI\autowire(AppConfig::class),

        // Acl roles, resources, permissions and resource factory
        AclRolesCollectorInterface::class               => DI\autowire(AclRolesCollector::class),
        AclResourcesCollectorInterface::class           => DI\autowire(AclResourcesCollector::class),
        AclRulesCollectorInterface::class               => DI\autowire(AclRulesCollector::class),
        AclResourceFactoryInterface::class              => DI\autowire(AclResourceFactory::class),

        // Use Twig as default view
        ViewFactoryInterface::class                     => DI\autowire(TwigViewFactory::class),
        // Use Twig in layouts
        LayoutViewInterface::class                      => DI\autowire(TwigLayoutView::class),

        // Custom access resolver detector
        ApiMethodAccessResolverDetectorInterface::class => DI\autowire(CustomApiMethodAccessResolverDetector::class),

        // Use default renderer for notification messages
        MessageRendererInterface::class                 => DI\autowire(MessageRenderer::class),

        Meta::class => \DI\factory(function () {
            throw new LogicException('DI injection of class Meta is deprecated');
        }),

        StaticAssets::class => \DI\factory(function () {
            throw new LogicException('DI injection of class StaticAssets is deprecated');
        }),

        ExternalEventTransportInterface::class => DI\autowire(EsbExternalEventTransport::class),

        EventBusInterface::class   => DI\factory(static function (EventBus $bus, ConfigProviderInterface $config) {
            foreach ((array)$config->load(['events']) as $event => $handlers) {
                foreach ($handlers as $handler) {
                    $bus->on($event, $handler);
                }
            }

            return $bus;
        }),

        // Handlers will be added in workers
        CommandBusInterface::class => DI\autowire(CommandBus::class),
    ],

];
