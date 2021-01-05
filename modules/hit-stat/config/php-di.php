<?php

use BetaKiller\Repository\HitPageRepository;
use BetaKiller\Repository\HitPageRepositoryInterface;
use function DI\autowire;

return [

    'definitions' => [
        // Repositories
        HitPageRepositoryInterface::class => autowire(HitPageRepository::class),
    ],

];
