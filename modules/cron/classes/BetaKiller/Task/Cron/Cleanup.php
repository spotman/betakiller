<?php

declare(strict_types=1);

namespace BetaKiller\Task\Cron;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
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
        $this->repo = $repo;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $this->repo->removeRecordsOlderThan(new \DateTimeImmutable('-60 days'));
    }
}
