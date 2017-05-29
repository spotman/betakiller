<?php

use Doctrine\Common\Cache\ArrayCache;
use Spotman\Acl\AclInterface;

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache' => new ArrayCache(),

    'definitions' => [

        AclInterface::DI_CACHE_OBJECT_KEY => DI\object(ArrayCache::class),

    ],

];
