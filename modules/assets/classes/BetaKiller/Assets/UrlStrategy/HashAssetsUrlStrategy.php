<?php
namespace BetaKiller\Assets\UrlStrategy;


use BetaKiller\Repository\HashUrlStrategyRepositoryInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;

class HashAssetsUrlStrategy implements AssetsUrlStrategyInterface
{
    /**
     * @var \BetaKiller\Repository\HashUrlStrategyRepositoryInterface
     */
    private $repository;

    /**
     * HashAssetsUrlStrategy constructor.
     *
     * @param \BetaKiller\Repository\HashUrlStrategyRepositoryInterface $repository
     */
    public function __construct(HashUrlStrategyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Find asset model by dispatchable part of url and return it
     *
     * @param string $path
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface|null
     */
    public function getModelFromFilename(string $path): ?AssetsModelInterface
    {
        $hash = basename($path);

        return $this->repository->findByHash($hash);
    }

    /**
     * Get dispatchable part of url for provided model
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     */
    public function getFilenameFromModel(AssetsModelInterface $model): string
    {
        return $model->getHash();
    }

}
