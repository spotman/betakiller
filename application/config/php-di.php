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

        \Model_User::class   => DI\get('User'),

        \BetaKiller\Model\User::class   => DI\get('User'),

        \BetaKiller\Config\ConfigInterface::class =>  DI\factory(function() {
            return new \BetaKiller\Config\Config();
        }),

        // Inject container
        // TODO anti-pattern
        \BetaKiller\DI\ContainerInterface::class =>  DI\factory(function() {
            return \BetaKiller\DI\Container::instance();
        }),

        \BetaKiller\Config\AppConfigInterface::class => DI\get(\BetaKiller\Config\AppConfig::class),

        // Use Twig in ifaces and layouts
        View_IFace::class   => DI\get(ViewIFaceTwig::class),
        View_Layout::class  => DI\get(ViewLayoutTwig::class),
        View_Wrapper::class => DI\get(ViewWrapperTwig::class),

    ],

];
