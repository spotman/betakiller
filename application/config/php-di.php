<?php

use \Doctrine\Common\Cache\ArrayCache;

return [

    'cache'         =>  new ArrayCache(),

    'annotations'   =>  true,
    'autowiring'    =>  true,

    'definitions'   =>  [

        Auth::class  =>  DI\factory(function() {
            return Auth::instance();
        }),

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

        \BetaKiller\DI\ContainerInterface::class =>  DI\factory(function() {
            return \BetaKiller\DI\Container::instance();
        }),

        \BetaKiller\Config\AppConfigInterface::class => DI\get(\BetaKiller\Config\AppConfig::class),

    ],

];
