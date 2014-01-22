<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'client'        =>  array(
        'version'   =>  API::VERSION,
        'proxy'     =>  API_Proxy::INTERNAL,

        'server'    =>  API_Server::JSON_RPC,
        'host'      =>  'test.spotman.ru',

    ),

    'server'    => array(
        'enabled'   =>  TRUE,
        'version'   =>  API::VERSION,
    ),
);