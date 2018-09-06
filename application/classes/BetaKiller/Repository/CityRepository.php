<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\City;
use BetaKiller\Model\CityInterface;

class CityRepository extends AbstractOrmBasedRepository implements CityRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \BetaKiller\Model\CityInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findByMaxmindId(int $id): ?CityInterface
    {
        $orm = $this
            ->getOrmInstance()
            ->where(City::TABLE_FIELD_MAXMIND_ID, '=', $id);

        return $this->findOne($orm);
    }
}
