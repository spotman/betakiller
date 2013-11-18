<?php defined('SYSPATH') OR die('No direct script access.');

return array(
    'proxy'     =>  API_Proxy::INTERNAL,
    'transport' =>  API_Transport::JSON_RPC,
    'version'   =>  API::VERSION,
    'host'      =>  'test.spotman.ru',

    'server_enabled'    =>  FALSE,
);