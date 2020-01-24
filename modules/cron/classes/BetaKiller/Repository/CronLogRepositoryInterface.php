<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CronLogInterface;

/**
 * Interface CronLogRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method void save(CronLogInterface $entity)
 */
interface CronLogRepositoryInterface extends RepositoryInterface
{
    /**
     * @param \DateTimeImmutable $beforeDate
     */
    public function removeRecordsOlderThan(\DateTimeImmutable $beforeDate): void;

    /**
     * @param string             $taskName
     * @param array              $params
     * @param \DateTimeImmutable $afterDate
     *
     * @return bool
     */
    public function hasTaskRecordAfter(string $taskName, array $params, \DateTimeImmutable $afterDate): bool;
}
