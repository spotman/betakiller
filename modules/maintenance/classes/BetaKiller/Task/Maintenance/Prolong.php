<?php

declare(strict_types=1);

namespace BetaKiller\Task\Maintenance;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Service\MaintenanceModeService;
use BetaKiller\Task\AbstractTask;

class Prolong extends AbstractTask
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
        // No action if maintenance is not enabled
        if (!$this->service->isEnabled()) {
            return;
        }

        $durationSpec = $params->getString(self::ARG_FOR);

        $durationSpec = sprintf('PT%uS', (int)$durationSpec);

        $duration = new \DateInterval($durationSpec);

        $now = new \DateTimeImmutable();

        $this->service->schedule($now, $now->add($duration));
    }
}
