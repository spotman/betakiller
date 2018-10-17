<?php
declare(strict_types=1);

namespace BetaKiller\Assets\PathStrategy;

use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\MultiLevelPath;
use BetaKiller\Repository\HashUrlStrategyRepositoryInterface;

class MultiLevelHashAssetsPathStrategy implements AssetsPathStrategyInterface
{
    /**
     * @var \BetaKiller\Repository\HashUrlStrategyRepositoryInterface
     */
    private $repository;

    /**
     * @var \BetaKiller\Assets\MultiLevelPath
     */
    private $multiLevelPath;

    /**
     * MultiLevelHashAssetsPathStrategy constructor.
     *
     * @param \BetaKiller\Repository\HashUrlStrategyRepositoryInterface $repository
     */
    public function __construct(HashUrlStrategyRepositoryInterface $repository)
    {
        $this->repository = $repository;

        $this->multiLevelPath = new MultiLevelPath(); // Use default, maybe would be configured in the future
    }

    /**
     * Find asset model by dispatchable part of url and return it
     *
     * @param string $path
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function getModelByPath(string $path): ?AssetsModelInterface
    {
        // Drop multi level paths
        $hash = $this->multiLevelPath->parse($path);

        return $this->repository->findByHash($hash);
    }

    /**
     * Get dispatchable part of url for provided model
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function makeModelPath(AssetsModelInterface $model): string
    {
        $hash = $model->getHash();

        if (!$hash) {
            throw new AssetsException('Asset has no hash :object', [
                ':object' => json_encode($model->toJson()),
            ]);
        }

        return $this->multiLevelPath->make($hash);
    }
}
