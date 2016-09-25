<?php

use \Doctrine\Common\Cache\ArrayCache;

return [

    'cache'         =>  new ArrayCache(),

    'definitions'   =>  [

        Auth::class  =>  DI\factory(function() {
            return Auth::instance();
        }),

        // Helpers
        'User'  =>  DI\factory(function() {
            $auth = Auth::instance();
            return $auth->get_user();
        }),

        \BetaKiller\Model\User::class   => DI\get('User'),

    ],

];
