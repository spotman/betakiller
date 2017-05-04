<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'deploy'    =>  [
        'enabled'           =>  in_array(Kohana::$environment, [Kohana::STAGING, Kohana::PRODUCTION]),
        'directory_mask'    =>  0775,
    ],

//    'providers' => array(
//        'codename'  =>  array(
//            'url_key'  =>  ''
//        )
//    )

);
