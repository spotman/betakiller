<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationLog;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Model\UserInterface;

/**
 * Class NotificationLogRepository
 *
 * @package BetaKiller\Repository
 * @method save(NotificationLogInterface $entity) : void
 */
class NotificationLogRepository extends AbstractOrmBasedDispatchableRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return NotificationLog::TABLE_COLUMN_ID;
    }

    /**
     * @param int $page
     * @param int $itemsPerPage
     *
     * @return \BetaKiller\Model\NotificationLogInterface[]
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getList(int $page, int $itemsPerPage): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->orderByProcessedAtDesc($orm)
            ->findAll($orm, $page, $itemsPerPage);
    }

    /**
     * @param string $messageCodename
     * @param int    $page
     * @param int    $itemsPerPage
     *
     * @return \BetaKiller\Model\NotificationLogInterface[]
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getMessageList(string $messageCodename, int $page, int $itemsPerPage): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterMessageCodename($orm, $messageCodename)
            ->orderByProcessedAtDesc($orm)
            ->findAll($orm, $page, $itemsPerPage);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     * @param int                             $page
     * @param int                             $itemsPerPage
     *
     * @return \BetaKiller\Model\NotificationLogInterface[]
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUserList(UserInterface $user, int $page, int $itemsPerPage): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUser($orm, $user)
            ->orderByProcessedAtDesc($orm)
            ->findAll($orm, $page, $itemsPerPage);
    }

    private function orderByProcessedAtDesc(ExtendedOrmInterface $orm): self
    {
        $orm->order_by($orm->object_column(NotificationLog::TABLE_COLUMN_PROCESSED_AT), 'desc');

        return $this;
    }

    private function filterMessageCodename(ExtendedOrmInterface $orm, string $codename): self
    {
        $orm->where($orm->object_column(NotificationLog::TABLE_COLUMN_MESSAGE_NAME), '=', $codename);

        return $this;
    }

    private function filterUser(ExtendedOrmInterface $orm, UserInterface $user): self
    {
        $orm->where($orm->object_column(NotificationLog::TABLE_COLUMN_USER_ID), '=', $user->getID());

        return $this;
    }
}
