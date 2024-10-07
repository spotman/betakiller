<?php

declare(strict_types=1);

namespace BetaKiller\Task\Maintenance;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Service\MaintenanceModeService;
use BetaKiller\Task\AbstractTask;

class Off extends AbstractTask
{
    /**
     * @var \BetaKiller\Service\MaintenanceModeService
     */
    private $service;

    /**
     * On constructor.
     *
     * @param \BetaKiller\Service\MaintenanceModeService $service
     */
    public function __construct(MaintenanceModeService $service)
    {
        $this->service = $service;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @return array
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $this->service->disable();
    }
}
