<?php

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache' =>  NULL,

    'definitions'       =>  [

//        \URL_Dispatcher::class  =>  DI\object(\URL_Dispatcher::class),

        \URL_Parameters::class  =>  DI\factory(function() {
            return \URL_Parameters::instance();
        })->scope(\DI\Scope::PROTOTYPE),

//        \IFace_Provider::class  =>  DI\object(\IFace_Provider::class),

//        \IFace_Model_Provider::class  =>  DI\object(\IFace_Model_Provider::class),

//        \IFace_Model_Provider::class  =>  DI\object(\IFace_Model_Provider::class),

    ],

];
