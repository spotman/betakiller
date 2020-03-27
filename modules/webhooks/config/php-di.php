<?php
declare(strict_types=1);

use BetaKiller\Repository\WebHookRepository;
use BetaKiller\Repository\WebHookRepositoryInterface;
use function DI\autowire;

return [
    'definitions' => [
        WebHookRepositoryInterface::class => autowire(WebHookRepository::class),
    ],
];
