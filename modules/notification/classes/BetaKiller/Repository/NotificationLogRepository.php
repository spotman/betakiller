<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationLog;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Query\NotificationLogQuery;
use BetaKiller\Search\SearchResultsInterface;

/**
 * Class NotificationLogRepository
 *
 * @package BetaKiller\Repository
 */
class NotificationLogRepository extends AbstractOrmBasedDispatchableRepository implements
    NotificationLogRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return NotificationLog::COL_HASH;
    }

    /**
     * @param string $hash
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByHash(string $hash): NotificationLogInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterHash($orm, $hash)
            ->getOne($orm);
    }

    /**
     * @inheritDoc
     */
    public function search(NotificationLogQuery $query, int $page, int $itemsPerPage): SearchResultsInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->applyQuery($orm, $query)
            ->orderByProcessedAtDesc($orm)
            ->findAllResults($orm, $page, $itemsPerPage);
    }

    private function orderByProcessedAtDesc(ExtendedOrmInterface $orm): self
    {
        $orm->order_by($orm->object_column(NotificationLog::COL_PROCESSED_AT), 'desc');

        return $this;
    }

    private function filterMessageCodename(ExtendedOrmInterface $orm, string $codename): self
    {
        $orm->where($orm->object_column(NotificationLog::COL_MESSAGE_NAME), '=', $codename);

        return $this;
    }

    private function filterHash(ExtendedOrmInterface $orm, string $hash): self
    {
        $orm->where($orm->object_column(NotificationLog::COL_HASH), '=', $hash);

        return $this;
    }

    private function filterTargetIdentity(ExtendedOrmInterface $orm, string $identity): self
    {
        $orm->where($orm->object_column(NotificationLog::COL_TARGET), '=', $identity);

        return $this;
    }

    private function filterUser(ExtendedOrmInterface $orm, UserInterface $user): self
    {
        $orm->where($orm->object_column(NotificationLog::COL_USER_ID), '=', $user->getID());

        return $this;
    }

    private function filterStatus(ExtendedOrmInterface $orm, string $codename): self
    {
        $orm->where($orm->object_column(NotificationLog::COL_STATUS), '=', $codename);

        return $this;
    }

    private function filterTransport(ExtendedOrmInterface $orm, string $codename): self
    {
        $orm->where($orm->object_column(NotificationLog::COL_TRANSPORT), '=', $codename);

        return $this;
    }

    private function applyQuery(ExtendedOrmInterface $orm, NotificationLogQuery $query): self
    {
        if ($query->hasTargetDefined()) {
            $this->filterTargetIdentity($orm, $query->getTargetIdentity());
        }

        if ($query->hasUserDefined()) {
            $this->filterUser($orm, $query->getUser());
        }

        if ($query->hasMessageCodenameDefined()) {
            $this->filterMessageCodename($orm, $query->getMessageCodename());
        }

        if ($query->hasStatusDefined()) {
            $this->filterStatus($orm, $query->getStatus());
        }

        if ($query->hasTransportDefined()) {
            $this->filterTransport($orm, $query->getTransport());
        }

        return $this;
    }
}
