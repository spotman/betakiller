<?php
namespace BetaKiller\Repository;

use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

abstract class AbstractOrmBasedHashStrategyAssetsRepository extends AbstractOrmBasedRepository implements
    HashStrategyAssetsRepositoryInterface
{
    public function findByHash(string $hash): ?AssetsModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterHash($orm, $hash)
            ->findOne($orm);
    }

    protected function filterHash(OrmInterface $orm, string $hash): self
    {
        $orm->where($orm->object_column('hash'), '=', $hash);

        return $this;
    }
}
