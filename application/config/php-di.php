<?php

use BetaKiller\Acl\AclRulesCollector;
use BetaKiller\Acl\AclResourceFactory;
use BetaKiller\Acl\AclResourcesCollector;
use BetaKiller\Acl\AclRolesCollector;
use BetaKiller\Api\AccessResolver\CustomApiMethodAccessResolverDetector;
use BetaKiller\Factory\CommonFactoryCache;
use BetaKiller\Factory\FactoryCacheInterface;
use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\IFace\Url\UrlDispatcherCache;
use BetaKiller\IFace\Url\UrlDispatcherCacheInterface;
use BetaKiller\IFace\View\IFaceView;
use BetaKiller\IFace\View\LayoutView;
use BetaKiller\IFace\View\WrapperView;
use BetaKiller\Model\GuestUser;
use BetaKiller\View\IFaceViewTwig;
use BetaKiller\View\LayoutViewTwig;
use BetaKiller\View\WrapperViewTwig;
use DI\Scope;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PhpFileCache;
use Psr\Log\LoggerInterface;
use Spotman\Acl\AclInterface;
use Spotman\Acl\RulesCollector\AclRulesCollectorInterface;
use Spotman\Acl\ResourceFactory\AclResourceFactoryInterface;
use Spotman\Acl\ResourcesCollector\AclResourcesCollectorInterface;
use Spotman\Acl\RolesCollector\AclRolesCollectorInterface;
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

    'namespace' => ($workingName ?: 'core').'-php-di-'.\Kohana::$environment_string,

    'annotations' => true,
    'autowiring'  => true,

    'definitions' => [

        // Always create new instance of this basic factory implementation coz it is configured in each factory
        NamespaceBasedFactory::class             => DI\object(NamespaceBasedFactory::class)->scope(Scope::PROTOTYPE),

        // Single cache instance for whole project
        FactoryCacheInterface::class             => DI\factory(function () use ($workingPath) {
            return new CommonFactoryCache([
                new PhpFileCache(implode(DIRECTORY_SEPARATOR, [$workingPath, 'cache', 'factory'])),
            ]);
        })->scope(Scope::SINGLETON),

        // Cache for url dispatcher
        UrlDispatcherCacheInterface::class       => DI\factory(function () use ($workingPath) {
            return new UrlDispatcherCache([
                new PhpFileCache(implode(DIRECTORY_SEPARATOR, [$workingPath, 'cache', 'dispatcher'])),
            ]);
        })->scope(Scope::SINGLETON),

        // Inject container into factories
        \BetaKiller\DI\ContainerInterface::class => DI\factory(function () {
            return \BetaKiller\DI\Container::getInstance();
        })->scope(Scope::SINGLETON),

        LoggerInterface::class => DI\object(\BetaKiller\Log\Logger::class)->lazy(),

        Auth::class => DI\factory(function () {
            return Auth::instance();
        })->scope(\DI\Scope::SINGLETON),

        // Helpers
        'User'      => DI\factory(function (Auth $auth) {
            $user = $auth->get_user();

            if (!$user) {
                $user = new GuestUser();
            }

            return $user;
        }),

        \BetaKiller\Config\ConfigInterface::class    => DI\object(\BetaKiller\Config\Config::class),
        \BetaKiller\Config\AppConfigInterface::class => DI\object(\BetaKiller\Config\AppConfig::class),

        \BetaKiller\Model\UserInterface::class => DI\get('User'),
        \BetaKiller\Model\RoleInterface::class  => DI\get(\BetaKiller\Model\Role::class),

        // Backward compatibility fix
        \Model_User::class                      => DI\object(\BetaKiller\Model\User::class)->scope(\DI\Scope::PROTOTYPE),
        \Model_Role::class                      => DI\object(\BetaKiller\Model\Role::class)->scope(\DI\Scope::PROTOTYPE),

        // Cache for production and staging (dev and testing has ArrayCache); use filesystem cache so it would be cleared after deployment
        AclInterface::DI_CACHE_OBJECT_KEY               => new FilesystemCache(implode(DIRECTORY_SEPARATOR, [$workingPath, 'cache', 'acl'])),

        // Acl roles, resources, permissions and resource factory
        AclRolesCollectorInterface::class               => DI\object(AclRolesCollector::class),
        AclResourcesCollectorInterface::class           => DI\object(AclResourcesCollector::class),
        AclRulesCollectorInterface::class               => DI\object(AclRulesCollector::class),
        AclResourceFactoryInterface::class              => DI\object(AclResourceFactory::class),

        // Use Twig in ifaces and layouts
        IFaceView::class                                => DI\object(IFaceViewTwig::class),
        LayoutView::class                               => DI\object(LayoutViewTwig::class),
        WrapperView::class                              => DI\object(WrapperViewTwig::class),

        // Custom access resolver detector
        ApiMethodAccessResolverDetectorInterface::class => DI\object(CustomApiMethodAccessResolverDetector::class),

    ],

];
