<?php

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Repository\NotificationFrequencyRepository;
use BetaKiller\Repository\NotificationFrequencyRepositoryInterface;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\NotificationGroupRepositoryInterface;
use BetaKiller\Repository\NotificationGroupUserConfigRepository;
use BetaKiller\Repository\NotificationGroupUserConfigRepositoryInterface;
use BetaKiller\Repository\NotificationLogRepository;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use function DI\autowire;

return [

    'definitions' => [

        NotificationConfigInterface::class                    => autowire(NotificationConfig::class),
        NotificationFrequencyRepositoryInterface::class       => autowire(NotificationFrequencyRepository::class),
        NotificationLogRepositoryInterface::class             => autowire(NotificationLogRepository::class),
        NotificationGroupRepositoryInterface::class           => autowire(NotificationGroupRepository::class),
        NotificationGroupUserConfigRepositoryInterface::class => autowire(NotificationGroupUserConfigRepository::class),

    ],

];
