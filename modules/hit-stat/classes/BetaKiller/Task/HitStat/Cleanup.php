<?php
declare(strict_types=1);

namespace BetaKiller\Task\HitStat;

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
        parent::__construct();

        $this->hitRepo = $hitRepo;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(): array
    {
        return [
            // No options
        ];
    }

    public function run(): void
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
