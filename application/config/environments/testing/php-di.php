<?php

use Doctrine\Common\Cache\ArrayCache;
use Spotman\Acl\AclInterface;

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'cache' => new ArrayCache(),

    'definitions' => [

    ],

];
