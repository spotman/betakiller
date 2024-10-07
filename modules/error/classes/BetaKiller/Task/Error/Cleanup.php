<?php

declare(strict_types=1);

namespace BetaKiller\Task\Error;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Repository\PhpExceptionRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use DateTimeImmutable;

final class Cleanup extends AbstractTask
{
    /**
     * @var \BetaKiller\Repository\PhpExceptionRepositoryInterface
     */
    private PhpExceptionRepositoryInterface $repo;

    /**
     * Cleanup constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepositoryInterface $repo
     */
    public function __construct(PhpExceptionRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        // No cli arguments
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $before = new DateTimeImmutable('- 2 weeks');

        foreach ($this->repo->getReadyForCleanup($before) as $item) {
            $this->repo->delete($item);
        }
    }
}
