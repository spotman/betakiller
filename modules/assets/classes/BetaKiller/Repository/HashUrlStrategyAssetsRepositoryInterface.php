<?php
namespace BetaKiller\Repository;

use BetaKiller\Assets\Model\AssetsModelInterface;

interface HashUrlStrategyAssetsRepositoryInterface extends RepositoryInterface
{
    public function findByHash(string $hash): ?AssetsModelInterface;
}
