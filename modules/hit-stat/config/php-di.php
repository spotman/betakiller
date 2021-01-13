<?php

use BetaKiller\Repository\HitDomainRepository;
use BetaKiller\Repository\HitDomainRepositoryInterface;
use BetaKiller\Repository\HitPageRepository;
use BetaKiller\Repository\HitPageRepositoryInterface;
use BetaKiller\Repository\HitRepository;
use BetaKiller\Repository\HitRepositoryInterface;
use function DI\autowire;

return [

    'definitions' => [
        // Repositories
        HitRepositoryInterface::class       => autowire(HitRepository::class),
        HitPageRepositoryInterface::class   => autowire(HitPageRepository::class),
        HitDomainRepositoryInterface::class => autowire(HitDomainRepository::class),
    ],

];
