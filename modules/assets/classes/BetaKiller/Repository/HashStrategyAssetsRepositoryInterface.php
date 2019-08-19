<?php
namespace BetaKiller\Repository;

use BetaKiller\Assets\Model\AssetsModelInterface;

interface HashStrategyAssetsRepositoryInterface extends AssetsModelRepositoryInterface
{
    public function findByHash(string $hash): ?AssetsModelInterface;
}
