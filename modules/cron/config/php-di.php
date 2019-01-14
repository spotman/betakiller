<?php

use BetaKiller\Repository\CronLogRepository;
use BetaKiller\Repository\CronLogRepositoryInterface;

return [

    'definitions' => [
        CronLogRepositoryInterface::class => \DI\autowire(CronLogRepository::class),
    ],

];
