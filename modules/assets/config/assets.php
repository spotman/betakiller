<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'deploy'    =>  [
        'enabled'           =>  (Kohana::$environment == Kohana::PRODUCTION),
        'directory_mask'    =>  0775,
    ],

//    'providers' => array(
//        'provider-key'  =>  array(
//            'codename'  =>  ''
//        )
//    )

);
