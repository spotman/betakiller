<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Query\NotificationLogQuery;

/**
 * Class NotificationLogRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method save(NotificationLogInterface $entity) : void
 */
interface NotificationLogRepositoryInterface extends DispatchableRepositoryInterface
{
    /**
     * @param string $hash
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByHash(string $hash): NotificationLogInterface;

    /**
     * @param \BetaKiller\Query\NotificationLogQuery $query
     * @param int                                    $page
     * @param int                                    $itemsPerPage
     *
     * @return \BetaKiller\Model\NotificationLogInterface[]
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getList(NotificationLogQuery $query, int $page, int $itemsPerPage): array;
}
