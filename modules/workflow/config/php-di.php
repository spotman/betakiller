<?php
declare(strict_types=1);

use BetaKiller\Config\WorkflowConfig;
use BetaKiller\Config\WorkflowConfigInterface;
use function DI\autowire;

return [
    'definitions' => [
        WorkflowConfigInterface::class => autowire(WorkflowConfig::class),
    ],
];
