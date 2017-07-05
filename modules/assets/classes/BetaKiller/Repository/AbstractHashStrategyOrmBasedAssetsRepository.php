<?php
namespace BetaKiller\Repository;

use BetaKiller\Assets\Model\AssetsModelInterface;

abstract class AbstractHashStrategyOrmBasedAssetsRepository extends AbstractOrmBasedRepository implements HashUrlStrategyRepositoryInterface
{
    public function findByHash(string $hash): ?AssetsModelInterface
    {
        /** @var \BetaKiller\Assets\Model\OrmBasedAssetsModelInterface $model */
        $model = $this->getOrmInstance()->where('hash', '=', $hash)->find();

        return $model->loaded() ? $model : null;
    }
}
