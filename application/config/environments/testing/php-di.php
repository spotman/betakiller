<?php

use Doctrine\Common\Cache\ArrayCache;
use Spotman\Acl\Acl;

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache'     =>  new ArrayCache(),

    'definitions'   => [

        Acl::DI_CACHE_OBJECT_KEY => DI\get(ArrayCache::class),

    ],

];
