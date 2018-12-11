<?php

use BetaKiller\Config\WampConfig;
use BetaKiller\Config\WampConfigInterface;
use function DI\autowire;

return [

    'definitions' => [
        WampConfigInterface::class => autowire(WampConfig::class),
    ],

];
