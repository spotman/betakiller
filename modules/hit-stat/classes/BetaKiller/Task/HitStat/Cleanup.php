<?php

declare(strict_types=1);

namespace BetaKiller\Task\HitStat;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Exception\DomainException;
use BetaKiller\Repository\HitRepositoryInterface;
use BetaKiller\Task\AbstractTask;

final class Cleanup extends AbstractTask
{
    /**
     * @var \BetaKiller\Repository\HitRepositoryInterface
     */
    private HitRepositoryInterface $hitRepo;

    /**
     * Cleanup constructor.
     *
     * @param \BetaKiller\Repository\HitRepositoryInterface $hitRepo
     */
    public function __construct(HitRepositoryInterface $hitRepo)
    {
        $this->hitRepo = $hitRepo;
    }

    /**
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            // No options
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $before = new \DateTimeImmutable('- 30 days');

        foreach ($this->hitRepo->getUnused($before) as $hit) {
            if ($hit->isProtected()) {
                throw new DomainException('Can not cleanup protected stat hit');
            }

            if (!$hit->isProcessed()) {
                throw new DomainException('Can not cleanup non-processed stat hit');
            }

            $this->hitRepo->delete($hit);
        }
    }
}
