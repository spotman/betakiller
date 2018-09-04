<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CountryInterface;
use BetaKiller\Model\ExtendedOrmInterface;

class CountryRepository extends AbstractOrmBasedRepository implements CountryRepositoryInterface
{
    public const FIELD_ISO_CODE = 'iso_code';

    /**
     * @param string $isoCode
     *
     * @return \BetaKiller\Model\CountryInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByIsoCode(string $isoCode): ?CountryInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByIsoCode($orm, $isoCode)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $isoCode
     *
     * @return \BetaKiller\Repository\CountryRepository
     */
    private function filterByIsoCode(ExtendedOrmInterface $orm, string $isoCode): self
    {
        $isoCode = strtoupper(trim($isoCode));
        $orm->where($orm->object_column(self::FIELD_ISO_CODE), '=', $isoCode);

        return $this;
    }
}
