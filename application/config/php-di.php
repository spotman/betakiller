<?php

use BetaKiller\View\ViewIFaceTwig;
use BetaKiller\View\ViewLayoutTwig;
use BetaKiller\View\ViewWrapperTwig;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;

use Spotman\Acl\Acl;
use Spotman\Acl\ResourcesCollector\ResourcesCollectorInterface;
use Spotman\Acl\RolesCollector\RolesCollectorInterface;
use Spotman\Acl\PermissionsCollector\PermissionsCollectorInterface;
use Spotman\Acl\ResourceFactory\ResourceFactoryInterface;

use BetaKiller\Acl\RolesCollector;
use BetaKiller\Acl\ResourcesCollector;
use BetaKiller\Acl\PermissionsCollector;
use BetaKiller\Acl\ResourceFactory;

$site_path = MultiSite::instance()->site_path();
$site_name = MultiSite::instance()->site_name();

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache'         =>  new ChainCache([
        new ArrayCache(),
        new FilesystemCache($site_path.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'php-di'),
    ]),

    'namespace'     =>  $site_name.'-php-di-'.\Kohana::$environment_string,

    'annotations'   =>  true,
    'autowiring'    =>  true,

    'definitions'   =>  [

        \Psr\Log\LoggerInterface::class =>  DI\factory(function() {
            return \BetaKiller\Log\Logger::getInstance();
        })->scope(\DI\Scope::SINGLETON),

        Auth::class  =>  DI\factory(function() {
            return Auth::instance();
        })->scope(\DI\Scope::SINGLETON),

        // Helpers
        'User'  =>  DI\factory(function(Auth $auth) {
//            $auth = Auth::instance();
            $user = $auth->get_user();

            if (!$user) {
                // TODO Create stub class for "Guest" user linked to "guest" group
                $user = ORM::factory('User');
            }

            return $user;
        }),

        \BetaKiller\Model\UserInterface::class  => DI\get('User'),
        \BetaKiller\Model\RoleInterface::class  => DI\get(\BetaKiller\Model\Role::class),

        // Backward compatibility fix
        \Model_User::class  =>  DI\object(\BetaKiller\Model\User::class)->scope(\DI\Scope::PROTOTYPE),
        \Model_Role::class  =>  DI\object(\BetaKiller\Model\Role::class)->scope(\DI\Scope::PROTOTYPE),

        \BetaKiller\Config\ConfigInterface::class       =>  DI\get(\BetaKiller\Config\Config::class),
        \BetaKiller\Config\AppConfigInterface::class    =>  DI\get(\BetaKiller\Config\AppConfig::class),

        // Inject container
        // TODO anti-pattern
        \BetaKiller\DI\ContainerInterface::class =>  DI\factory(function() {
            return \BetaKiller\DI\Container::instance();
        }),

        // Define cache for production and staging env (dev and testing has ArrayCache)
        Acl::DI_CACHE_OBJECT_KEY => new FilesystemCache($site_path.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'acl'),

        // Acl roles, resources, permissions and resource factory
        RolesCollectorInterface::class          => DI\get(RolesCollector::class),
        ResourcesCollectorInterface::class      => DI\get(ResourcesCollector::class),
        PermissionsCollectorInterface::class    => DI\get(PermissionsCollector::class),
        ResourceFactoryInterface::class         => DI\get(ResourceFactory::class),

        // Use Twig in ifaces and layouts
        View_IFace::class   => DI\get(ViewIFaceTwig::class),
        View_Layout::class  => DI\get(ViewLayoutTwig::class),
        View_Wrapper::class => DI\get(ViewWrapperTwig::class),

    ],

];
