<?php
declare(strict_types=1);

namespace BetaKiller\Task\Cron;

use BetaKiller\Repository\CronLogRepositoryInterface;
use BetaKiller\Task\AbstractTask;

final class Cleanup extends AbstractTask
{
    /**
     * @var \BetaKiller\Repository\CronLogRepositoryInterface
     */
    private $repo;

    /**
     * Cleanup constructor.
     *
     * @param \BetaKiller\Repository\CronLogRepositoryInterface $repo
     */
    public function __construct(CronLogRepositoryInterface $repo)
    {
        parent::__construct();

        $this->repo = $repo;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->repo->removeRecordsOlderThan(new \DateTimeImmutable('-60 days'));
    }
}
