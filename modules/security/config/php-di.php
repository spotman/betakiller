<?php

declare(strict_types=1);

use BetaKiller\Security\Encryption;
use BetaKiller\Security\EncryptionInterface;

use function DI\{autowire};

return [
    'definitions' => [
        EncryptionInterface::class => autowire(Encryption::class),
    ],
];
