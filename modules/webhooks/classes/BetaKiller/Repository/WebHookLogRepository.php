<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;

class WebHookLogRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $codeName
     *
     * @return \BetaKiller\Model\WebHookLog[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getItems(string $codeName): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByCodename($orm, $codeName)
            ->orderByCreatedAtDesc($orm)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $codeName
     *
     * @return \BetaKiller\Repository\WebHookLogRepository
     */
    private function filterByCodename(ExtendedOrmInterface $orm, string $codeName): self
    {
        $orm->where('codename', '=', $codeName);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return \BetaKiller\Repository\WebHookLogRepository
     */
    private function orderByCreatedAtDesc(ExtendedOrmInterface $orm): self
    {
        $orm->order_by($orm->object_column('created_at'), 'DESC');

        return $this;
    }
}
