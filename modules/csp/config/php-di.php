<?php

declare(strict_types=1);

use BetaKiller\Security\SecurityConfig;
use BetaKiller\Security\SecurityConfigInterface;

use function DI\{autowire};

return [
    'definitions' => [
        SecurityConfigInterface::class => autowire(SecurityConfig::class),
    ],
];
