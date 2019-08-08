<?php
declare(strict_types=1);

use BetaKiller\Config\WorkflowConfig;
use BetaKiller\Config\WorkflowConfigInterface;
use BetaKiller\Workflow\StatusWorkflow;
use BetaKiller\Workflow\StatusWorkflowInterface;
use function DI\autowire;

return [
    'definitions' => [
        WorkflowConfigInterface::class => autowire(WorkflowConfig::class),
        StatusWorkflowInterface::class => autowire(StatusWorkflow::class),
    ],
];
