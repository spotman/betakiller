<?php

use BetaKiller\Repository\CronCommandRepository;
use BetaKiller\Repository\CronCommandRepositoryInterface;
use BetaKiller\Repository\CronLogRepository;
use BetaKiller\Repository\CronLogRepositoryInterface;
use function DI\autowire;

return [

    'definitions' => [
        CronLogRepositoryInterface::class     => autowire(CronLogRepository::class),
        CronCommandRepositoryInterface::class => autowire(CronCommandRepository::class),
    ],

];
