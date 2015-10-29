<?php

return [

    // Helpers
    'User'  =>  DI\factory(function() {
        $auth = Auth::instance();
        return $auth->get_user();
    }),

    'Auth'  =>  DI\factory(function() {
        return Auth::instance();
    }),

    \URL_Dispatcher::class  =>  DI\factory(function() {
        return URL_Dispatcher::instance();
    }),

    \BetaKiller\Model\User::class   => DI\get('User'),

];
