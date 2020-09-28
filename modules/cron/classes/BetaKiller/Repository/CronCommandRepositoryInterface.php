<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CronCommandInterface;

/**
 * Interface CronCommandRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method void save(CronCommandInterface $entity)
 */
interface CronCommandRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $taskName
     * @param array  $params
     *
     * @return \BetaKiller\Model\CronCommandInterface|null
     */
    public function findByNameAndParams(string $taskName, array $params): ?CronCommandInterface;

    /**
     * @param string $cmd
     *
     * @return \BetaKiller\Model\CronCommandInterface|null
     */
    public function findByCmd(string $cmd): ?CronCommandInterface;
}
