<?php

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Config\NotificationConfigInterface;

return [

    'definitions' => [

        NotificationConfigInterface::class => DI\autowire(NotificationConfig::class),

    ],

];
