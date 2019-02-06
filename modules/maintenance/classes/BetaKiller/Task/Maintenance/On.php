<?php
declare(strict_types=1);

namespace BetaKiller\Task\Maintenance;

use BetaKiller\Service\MaintenanceModeService;
use BetaKiller\Task\AbstractTask;

class On extends AbstractTask
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

        parent::__construct();
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [
            'for' => null,
        ];
    }

    public function run(): void
    {
        $durationSpec = $this->getOption('for', true);

        if ($durationSpec === 'deploy') {
            $durationSpec = 'PT30S';
        }

        $duration = new \DateInterval($durationSpec);

        $this->service->enable($duration);
    }
}
