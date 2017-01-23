<?php

use Doctrine\Common\Cache\ArrayCache;

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache'     =>  new ArrayCache(),

    'definitions'   => [

        'AclCache' => DI\get(ArrayCache::class),

    ],

];
