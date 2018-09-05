<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\City;

class CityRepository extends AbstractOrmBasedRepository implements CityRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \BetaKiller\Repository\CityRepositoryInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findByMaxmindId(int $id): ?CityRepositoryInterface
    {
        $orm = $this
            ->getOrmInstance()
            ->where(City::TABLE_FIELD_MAXMIND_ID, '=', $id);

        return $this->findOne($orm);
    }
}
