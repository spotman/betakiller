<?php

use BetaKiller\Config\WampConfig;
use BetaKiller\Config\WampConfigInterface;
use BetaKiller\Wamp\WampUserDb;
use Thruway\Authentication\WampCraUserDbInterface;
use function DI\autowire;

return [

    'definitions' => [
        WampConfigInterface::class    => autowire(WampConfig::class),
        WampCraUserDbInterface::class => autowire(WampUserDb::class),
    ],

];
