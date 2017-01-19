<?php

use BetaKiller\View\ViewIFaceTwig;
use BetaKiller\View\ViewLayoutTwig;
use BetaKiller\View\ViewWrapperTwig;
use \Doctrine\Common\Cache\ArrayCache;

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache'         =>  new ArrayCache(),
    'namespace'     =>  MultiSite::instance()->site_name() .'-php-di-'.\Kohana::$environment_string,

    'annotations'   =>  true,
    'autowiring'    =>  true,

    'definitions'   =>  [

        Auth::class  =>  DI\factory(function() {
            return Auth::instance();
        })->scope(\DI\Scope::SINGLETON),

        // Helpers
        'User'  =>  DI\factory(function() {
            $auth = Auth::instance();
            $user = $auth->get_user();

            if (!$user) {
                $user = ORM::factory('User');
            }

            return $user;
        }),

        \BetaKiller\Model\UserInterface::class  => DI\get('User'),

        \Model_User::class  =>  DI\object(\BetaKiller\Model\User::class)->scope(\DI\Scope::PROTOTYPE),
        \Model_Role::class  =>  DI\object(\BetaKiller\Model\Role::class)->scope(\DI\Scope::PROTOTYPE),

        \BetaKiller\Config\ConfigInterface::class       =>  DI\get(\BetaKiller\Config\Config::class),
        \BetaKiller\Config\AppConfigInterface::class    =>  DI\get(\BetaKiller\Config\AppConfig::class),

        // Inject container
        // TODO anti-pattern
        \BetaKiller\DI\ContainerInterface::class =>  DI\factory(function() {
            return \BetaKiller\DI\Container::instance();
        }),


        // Use Twig in ifaces and layouts
        View_IFace::class   => DI\get(ViewIFaceTwig::class),
        View_Layout::class  => DI\get(ViewLayoutTwig::class),
        View_Wrapper::class => DI\get(ViewWrapperTwig::class),

    ],

];
