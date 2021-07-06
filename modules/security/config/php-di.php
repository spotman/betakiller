<?php
declare(strict_types=1);

use BetaKiller\Security\Encryption;
use BetaKiller\Security\EncryptionInterface;
use BetaKiller\Security\SecurityConfig;
use BetaKiller\Security\SecurityConfigInterface;

use function DI\ {
    autowire
};

return [
    'definitions' => [
        EncryptionInterface::class     => autowire(Encryption::class),
        SecurityConfigInterface::class => autowire(SecurityConfig::class),
    ],
];
