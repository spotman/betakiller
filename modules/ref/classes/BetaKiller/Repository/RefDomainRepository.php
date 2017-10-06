<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\RefDomain;

/**
 * Class RefHitRepository
 *
 * @package BetaKiller\Repository
 * @method RefDomain findById(int $id)
 * @method RefDomain create()
 * @method RefDomain[] getAll()
 */
class RefDomainRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\RefDomain|null
     * @throws \Kohana_Exception
     */
    public function getByName(string $name): ?RefDomain
    {
        $orm = $this->getOrmInstance();

        $model = $orm->where('name', '=', $name)->find();

        return $model->loaded() ? $model : null;
    }
}
