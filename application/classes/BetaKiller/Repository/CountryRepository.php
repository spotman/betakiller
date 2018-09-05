<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\Country;
use BetaKiller\Model\CountryInterface;

class CountryRepository extends AbstractOrmBasedRepository implements CountryRepositoryInterface
{
    /**
     * @param string $isoCode
     *
     * @return \BetaKiller\Model\CountryInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findByIsoCode(string $isoCode): ?CountryInterface
    {
        $orm = $this
            ->getOrmInstance()
            ->where(Country::TABLE_FIELD_ISO_CODE, '=', $isoCode);

        return $this->findOne($orm);
    }
}
