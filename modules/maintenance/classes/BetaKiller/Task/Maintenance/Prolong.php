<?php
declare(strict_types=1);

namespace BetaKiller\Task\Maintenance;

use BetaKiller\Service\MaintenanceModeService;
use BetaKiller\Task\AbstractTask;

class Prolong extends AbstractTask
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
        // No action if maintenance is not enabled
        if (!$this->service->isEnabled()) {
            return;
        }

        $durationSpec = $this->getOption('for', true);

        $durationSpec = sprintf('PT%uS', (int)$durationSpec);

        $duration = new \DateInterval($durationSpec);

        $now = new \DateTimeImmutable();

        $this->service->schedule($now, $now->add($duration));
    }
}
