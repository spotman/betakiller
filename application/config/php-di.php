<?php

use BetaKiller\Acl\PermissionsCollector;
use BetaKiller\Acl\ResourceFactory;
use BetaKiller\Acl\ResourcesCollector;
use BetaKiller\Acl\RolesCollector;
use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\Model\GuestUser;
use BetaKiller\View\ViewIFaceTwig;
use BetaKiller\View\ViewLayoutTwig;
use BetaKiller\View\ViewWrapperTwig;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Psr\Log\LoggerInterface;
use Spotman\Acl\Acl;
use Spotman\Acl\PermissionsCollector\PermissionsCollectorInterface;
use Spotman\Acl\ResourceFactory\ResourceFactoryInterface;
use Spotman\Acl\ResourcesCollector\ResourcesCollectorInterface;
use Spotman\Acl\RolesCollector\RolesCollectorInterface;

$site_path = MultiSite::instance()->site_path();
$site_name = MultiSite::instance()->site_name();

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache' => new ChainCache([
        new ArrayCache(),
        new FilesystemCache(implode(DIRECTORY_SEPARATOR, [$site_path, 'cache', 'php-di'])),
    ]),

    'namespace' => $site_name.'-php-di-'.\Kohana::$environment_string,

    'annotations' => true,
    'autowiring'  => true,

    'definitions' => [

        // Always create new instance of this factory
        NamespaceBasedFactory::class             => DI\object(NamespaceBasedFactory::class)->scope(\DI\Scope::PROTOTYPE),

        // Inject container into factories
        \BetaKiller\DI\ContainerInterface::class => DI\factory(function () {
            return \BetaKiller\DI\Container::instance();
        }),

        LoggerInterface::class => DI\factory(function () {
            return \BetaKiller\Log\Logger::getInstance();
        })->scope(\DI\Scope::SINGLETON),

        Auth::class => DI\factory(function () {
            return Auth::instance();
        })->scope(\DI\Scope::SINGLETON),

        // Helpers
        'User'      => DI\factory(function (Auth $auth) {
//            $auth = Auth::instance();
            $user = $auth->get_user();

            if (!$user) {
                $user = new GuestUser();
            }

            return $user;
        }),

        \BetaKiller\Config\ConfigInterface::class    => DI\object(\BetaKiller\Config\Config::class),
        \BetaKiller\Config\AppConfigInterface::class => DI\object(\BetaKiller\Config\AppConfig::class),

        \BetaKiller\Model\UserInterface::class => DI\get('User'),
        \BetaKiller\Model\RoleInterface::class => DI\get(\BetaKiller\Model\Role::class),

        // Backward compatibility fix
        \Model_User::class                     => DI\object(\BetaKiller\Model\User::class)->scope(\DI\Scope::PROTOTYPE),
        \Model_Role::class                     => DI\object(\BetaKiller\Model\Role::class)->scope(\DI\Scope::PROTOTYPE),

        // Cache for production and staging (dev and testing has ArrayCache); use filesystem cache so it would be cleared after deployment
        Acl::DI_CACHE_OBJECT_KEY               => new FilesystemCache(implode(DIRECTORY_SEPARATOR, [$site_path, 'cache', 'acl'])),

        // Acl roles, resources, permissions and resource factory
        RolesCollectorInterface::class         => DI\object(RolesCollector::class),
        ResourcesCollectorInterface::class     => DI\object(ResourcesCollector::class),
        PermissionsCollectorInterface::class   => DI\object(PermissionsCollector::class),
        ResourceFactoryInterface::class        => DI\object(ResourceFactory::class),

        // Use Twig in ifaces and layouts
        View_IFace::class                      => DI\object(ViewIFaceTwig::class),
        View_Layout::class                     => DI\object(ViewLayoutTwig::class),
        View_Wrapper::class                    => DI\object(ViewWrapperTwig::class),

    ],

];
