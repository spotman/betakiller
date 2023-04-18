<?php
declare(strict_types=1);

use BetaKiller\Log\Logger;
use BetaKiller\Log\LoggerInterface;
use function DI\autowire;
use function DI\get;

return [
    'definitions' => [
        LoggerInterface::class => autowire(Logger::class),

        \Psr\Log\LoggerInterface::class => get(LoggerInterface::class),
    ],
];
