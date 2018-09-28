<?php

use BetaKiller\Acl\AclResourceFactory;
use BetaKiller\Acl\AclResourcesCollector;
use BetaKiller\Acl\AclRolesCollector;
use BetaKiller\Acl\AclRulesCollector;
use BetaKiller\Api\AccessResolver\CustomApiMethodAccessResolverDetector;
use BetaKiller\Auth\Auth;
use BetaKiller\Config\AppConfig;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Exception\ExceptionHandlerInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Notification\DefaultMessageRendered;
use BetaKiller\Notification\MessageRendererInterface;
use BetaKiller\Service\UserService;
use BetaKiller\View\LayoutViewInterface;
use BetaKiller\View\LayoutViewTwig;
use BetaKiller\View\TwigViewFactory;
use BetaKiller\View\ViewFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Spotman\Acl\ResourceFactory\AclResourceFactoryInterface;
use Spotman\Acl\ResourcesCollector\AclResourcesCollectorInterface;
use Spotman\Acl\RolesCollector\AclRolesCollectorInterface;
use Spotman\Acl\RulesCollector\AclRulesCollectorInterface;
use Spotman\Api\AccessResolver\ApiMethodAccessResolverDetectorInterface;

$workingPath = MultiSite::instance()->getWorkingPath();

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'compile_to'        => implode(DIRECTORY_SEPARATOR, [$workingPath, 'cache', 'php-di']),

    // Enable this only if your server has APCu enabled
    'cache_definitions' => false,

    'annotations' => true,
    'autowiring'  => true,

    'definitions' => [

        ExceptionHandlerInterface::class            => DI\autowire(\BetaKiller\Error\ExceptionHandler::class)->lazy(),

        // PSR-16 adapter for system-wide Doctrine Cache
        Psr\SimpleCache\CacheInterface::class       => DI\factory(function (\Doctrine\Common\Cache\Cache $doctrineCache
        ) {
            return new SimpleCacheAdapter($doctrineCache);
        }),

        // Bind Doctrine cache interface to abstract cache provider
        \Doctrine\Common\Cache\Cache::class         => DI\get(Doctrine\Common\Cache\CacheProvider::class),

        // Common cache instance for all
        \Doctrine\Common\Cache\CacheProvider::class => DI\get(\BetaKiller\Cache\DoctrineCacheProvider::class),

        ServerRequestInterface::class => DI\factory(function() {
            // TODO Remove this after moving to PSR-7
            return \Zend\Diactoros\ServerRequestFactory::fromGlobals();
        }),

        'User' => DI\factory(function (
            Auth $auth,
            ServerRequestInterface $request,
            AppEnvInterface $appEnv,
            UserService $userService,
            I18nHelper $i18n
        ) {
            $user = $auth->getUserFromRequest($request);

            $i18n->initFromUser($user);

            if ($userService->isDeveloper($user)) {
                $appEnv->enableDebug();
            }

            return $user;
        }),

        AppConfigInterface::class => DI\autowire(AppConfig::class),

        \BetaKiller\Model\UserInterface::class          => DI\get('User'),
        //\BetaKiller\Model\RoleInterface::class          => DI\get(\BetaKiller\Model\Role::class),

        // Acl roles, resources, permissions and resource factory
        AclRolesCollectorInterface::class               => DI\autowire(AclRolesCollector::class),
        AclResourcesCollectorInterface::class           => DI\autowire(AclResourcesCollector::class),
        AclRulesCollectorInterface::class               => DI\autowire(AclRulesCollector::class),
        AclResourceFactoryInterface::class              => DI\autowire(AclResourceFactory::class),

        // Use Twig as default view
        ViewFactoryInterface::class                     => DI\autowire(TwigViewFactory::class),
        // Use Twig in layouts
        LayoutViewInterface::class                      => DI\autowire(LayoutViewTwig::class),

        // Custom access resolver detector
        ApiMethodAccessResolverDetectorInterface::class => DI\autowire(CustomApiMethodAccessResolverDetector::class),

        // Use default renderer for notification messages
        MessageRendererInterface::class                 => DI\autowire(DefaultMessageRendered::class),

        Meta::class => \DI\factory(function () {
            return Meta::instance();
        }),

        \BetaKiller\MessageBus\EventBusInterface::class => DI\factory(function (
            \BetaKiller\MessageBus\EventBus $bus,
            ConfigProviderInterface $config
        ) {
            $eventsConfig = $config->load(['events']);

            if ($eventsConfig && is_array($eventsConfig)) {
                foreach ($eventsConfig as $event => $handlers) {
                    foreach ($handlers as $handler) {
                        $bus->on($event, $handler);
                    }
                }
            }

            return $bus;
        }),

        \BetaKiller\MessageBus\CommandBusInterface::class => DI\factory(function (
            \BetaKiller\MessageBus\CommandBus $bus,
            ConfigProviderInterface $config
        ) {
            $eventsConfig = $config->load(['commands']);

            if ($eventsConfig && is_array($eventsConfig)) {
                foreach ($eventsConfig as $event => $handler) {
                    $bus->on($event, $handler);
                }
            }

            return $bus;
        }),
    ],

];
