<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\UrlElementZone;
use BetaKiller\Url\ZoneInterface;

/**
 * Class UrlElementZoneRepository
 *
 * @package BetaKiller\IFace
 * @method save(ZoneInterface $entity) : void
 */
class UrlElementZoneRepository extends AbstractOrmBasedRepository
{
    public function findByName(string $name): ?ZoneInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterName($orm, $name)
            ->findOne($orm);
    }

    private function filterName(ExtendedOrmInterface $orm, string $value): self
    {
        $orm->where($orm->object_column(UrlElementZone::TABLE_FIELD_NAME), '=', $value);

        return $this;
    }
}
