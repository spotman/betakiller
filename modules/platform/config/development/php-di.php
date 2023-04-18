<?php
declare(strict_types=1);

use BetaKiller\FakeIdentityConverter;
use BetaKiller\IdentityConverterInterface;
use function DI\autowire;

return [
    'definitions' => [
        // No IDs hashing for simplicity during development
        IdentityConverterInterface::class => autowire(FakeIdentityConverter::class),
    ],
];
