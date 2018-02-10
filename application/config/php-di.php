<?php

use BetaKiller\Acl\AclResourceFactory;
use BetaKiller\Acl\AclResourcesCollector;
use BetaKiller\Acl\AclRolesCollector;
use BetaKiller\Acl\AclRulesCollector;
use BetaKiller\Api\AccessResolver\CustomApiMethodAccessResolverDetector;
use BetaKiller\Config\AppConfig;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Config\KohanaConfigProvider;
use BetaKiller\Exception\ExceptionHandlerInterface;
use BetaKiller\Helper\AppEnv;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Notification\DefaultMessageRendered;
use BetaKiller\Notification\MessageRendererInterface;
use BetaKiller\View\LayoutViewInterface;
use BetaKiller\View\LayoutViewTwig;
use BetaKiller\View\TwigViewFactory;
use BetaKiller\View\ViewFactoryInterface;
use DI\Scope;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Psr\Log\LoggerInterface;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Spotman\Acl\ResourceFactory\AclResourceFactoryInterface;
use Spotman\Acl\ResourcesCollector\AclResourcesCollectorInterface;
use Spotman\Acl\RolesCollector\AclRolesCollectorInterface;
use Spotman\Acl\RulesCollector\AclRulesCollectorInterface;
use Spotman\Api\AccessResolver\ApiMethodAccessResolverDetectorInterface;

$workingPath = MultiSite::instance()->getWorkingPath();
$workingName = MultiSite::instance()->getWorkingName();

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache' => new ChainCache([
        new ArrayCache(),
        new FilesystemCache(implode(DIRECTORY_SEPARATOR, [$workingPath, 'cache', 'php-di'])),
    ]),

    'namespace' => ($workingName ?: 'core').'-php-di-'.\Kohana::$environmentString,

    'annotations' => true,
    'autowiring'  => true,

    'definitions' => [

        ExceptionHandlerInterface::class            => DI\object(\BetaKiller\Error\ExceptionHandler::class),

        // PSR-16 adapter for system-wide Doctrine Cache
        Psr\SimpleCache\CacheInterface::class       => DI\factory(function (\Psr\Container\ContainerInterface $container
        ) {
            return new SimpleCacheAdapter($container->get(\Doctrine\Common\Cache\Cache::class));
        }),

        // Bind Doctrine cache interface to abstract cache provider
        \Doctrine\Common\Cache\Cache::class         => DI\get(Doctrine\Common\Cache\CacheProvider::class),

        // Common cache instance for all
        \Doctrine\Common\Cache\CacheProvider::class => DI\get(\BetaKiller\Cache\DoctrineCacheProvider::class),

        // Inject container into factories
        \BetaKiller\DI\ContainerInterface::class    => DI\factory(function () {
            return \BetaKiller\DI\Container::getInstance();
        })->scope(Scope::SINGLETON),

        // Use logger only when really needed
        LoggerInterface::class                      => DI\get(\BetaKiller\Log\Logger::class),

        AppEnvInterface::class => DI\factory(function () {
            $multiSite = MultiSite::instance();

            return new AppEnv(
                $multiSite->getWorkingPath(),
                $multiSite->docRoot(),
                !$multiSite->isSiteDetected()
            );
        }),

        Auth::class => DI\factory(function () {
            return Auth::instance();
        })->scope(\DI\Scope::SINGLETON),

        // Helpers
        'User'      => DI\factory(function (\BetaKiller\Helper\UserDetector $detector) {
            return $detector->detect();
        }),

        ConfigProviderInterface::class => DI\object(KohanaConfigProvider::class),
        AppConfigInterface::class      => DI\object(AppConfig::class),

        \BetaKiller\Model\UserInterface::class          => DI\get('User'),
        \BetaKiller\Model\RoleInterface::class          => DI\get(\BetaKiller\Model\Role::class),

        // Acl roles, resources, permissions and resource factory
        AclRolesCollectorInterface::class               => DI\object(AclRolesCollector::class),
        AclResourcesCollectorInterface::class           => DI\object(AclResourcesCollector::class),
        AclRulesCollectorInterface::class               => DI\object(AclRulesCollector::class),
        AclResourceFactoryInterface::class              => DI\object(AclResourceFactory::class),

        // Use Twig as default view
        ViewFactoryInterface::class                     => DI\object(TwigViewFactory::class),
        // Use Twig in layouts
        LayoutViewInterface::class                      => DI\object(LayoutViewTwig::class),

        // Custom access resolver detector
        ApiMethodAccessResolverDetectorInterface::class => DI\object(CustomApiMethodAccessResolverDetector::class),

        // Use default renderer for notification messages
        MessageRendererInterface::class                 => DI\object(DefaultMessageRendered::class),

        Meta::class => \DI\factory(function () {
            return Meta::instance();
        }),
    ],

];
