<?php

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Repository\NotificationGroupUserConfigRepository;
use BetaKiller\Repository\NotificationGroupUserConfigRepositoryInterface;
use BetaKiller\Repository\NotificationLogRepository;
use BetaKiller\Repository\NotificationLogRepositoryInterface;

return [

    'definitions' => [

        NotificationConfigInterface::class                    => DI\autowire(NotificationConfig::class),
        NotificationLogRepositoryInterface::class             => DI\autowire(NotificationLogRepository::class),
        NotificationGroupUserConfigRepositoryInterface::class => DI\autowire(NotificationGroupUserConfigRepository::class),

    ],

];
