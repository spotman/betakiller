<?php

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache' =>  NULL,

    'definitions'       =>  [

        \URL_Parameters::class  =>  DI\factory(function() {
            return \URL_Parameters::instance();
        })->scope(\DI\Scope::PROTOTYPE),

        \URL_Dispatcher::class  =>  DI\factory(function() {
            return \URL_Dispatcher::instance();
        })->scope(\DI\Scope::PROTOTYPE),

    ],

];
