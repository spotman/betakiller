<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\HitDomain;

/**
 * Class HitDomainRepository
 *
 * @package BetaKiller\Repository
 * @method HitDomain findById(int $id)
 * @method HitDomain create()
 * @method HitDomain[] getAll()
 */
class HitDomainRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\HitDomain|null
     */
    public function getByName(string $name): ?HitDomain
    {
        $orm = $this->getOrmInstance();

        $orm->where('name', '=', $name);

        return $this->findOne($orm);
    }
}
