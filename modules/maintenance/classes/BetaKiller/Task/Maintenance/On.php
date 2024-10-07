<?php

declare(strict_types=1);

namespace BetaKiller\Task\Maintenance;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Service\MaintenanceModeService;
use BetaKiller\Task\AbstractTask;

class On extends AbstractTask
{
    private const ARG_FOR = 'for';

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
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->string(self::ARG_FOR)->required(),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $durationSpec = $params->getString(self::ARG_FOR);

        if ($durationSpec === 'deploy') {
            $durationSpec = 30; // 30 seconds
        }

        $durationSpec = sprintf('PT%uS', (int)$durationSpec);

        $duration = new \DateInterval($durationSpec);

        $this->service->enable($duration);
    }
}
