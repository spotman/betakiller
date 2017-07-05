<?php
namespace BetaKiller\Assets\UrlStrategy;

use BetaKiller\Assets\Model\AssetsModelInterface;

interface AssetsUrlStrategyInterface
{
    /**
     * Find asset model by dispatchable part of url and return it
     *
     * @param string $path
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function getModelFromFilename(string $path): ?AssetsModelInterface;

    /**
     * Get dispatchable part of url for provided model
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     */
    public function getFilenameFromModel(AssetsModelInterface $model): string;
}
