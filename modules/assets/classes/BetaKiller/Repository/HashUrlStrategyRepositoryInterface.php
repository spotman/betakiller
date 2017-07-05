<?php
namespace BetaKiller\Repository;

use BetaKiller\Assets\Model\AssetsModelInterface;

interface HashUrlStrategyRepositoryInterface
{
    public function findByHash(string $hash): ?AssetsModelInterface;
}
