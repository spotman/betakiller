<?php
declare(strict_types=1);

use BetaKiller\Repository\PhpExceptionRepository;
use BetaKiller\Repository\PhpExceptionRepositoryInterface;
use function DI\autowire;

return [
    'definitions' => [
        PhpExceptionRepositoryInterface::class => autowire(PhpExceptionRepository::class),
    ],
];
