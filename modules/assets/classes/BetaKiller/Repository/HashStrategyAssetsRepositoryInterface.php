<?php
namespace BetaKiller\Repository;

use BetaKiller\Assets\Model\AssetsModelInterface;

interface HashStrategyAssetsRepositoryInterface extends RepositoryInterface
{
    public function findByHash(string $hash): ?AssetsModelInterface;
}
