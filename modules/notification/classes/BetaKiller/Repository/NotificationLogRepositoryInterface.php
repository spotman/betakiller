<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Model\UserInterface;

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
     * @param int $page
     * @param int $itemsPerPage
     *
     * @return \BetaKiller\Model\NotificationLogInterface[]
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getList(int $page, int $itemsPerPage): array;

    /**
     * @param string $messageCodename
     * @param int    $page
     * @param int    $itemsPerPage
     *
     * @return \BetaKiller\Model\NotificationLogInterface[]
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getMessageList(string $messageCodename, int $page, int $itemsPerPage): array;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     * @param int                             $page
     * @param int                             $itemsPerPage
     *
     * @return \BetaKiller\Model\NotificationLogInterface[]
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUserList(UserInterface $user, int $page, int $itemsPerPage): array;
}
