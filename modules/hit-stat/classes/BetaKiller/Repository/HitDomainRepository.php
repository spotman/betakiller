<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\HitDomainInterface;

/**
 * Class HitDomainRepository
 *
 * @package BetaKiller\Repository
 */
final class HitDomainRepository extends AbstractOrmBasedRepository implements HitDomainRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getByName(string $name): ?HitDomainInterface
    {
        $orm = $this->getOrmInstance();

        $orm->where('name', '=', $name);

        return $this->findOne($orm);
    }
}
