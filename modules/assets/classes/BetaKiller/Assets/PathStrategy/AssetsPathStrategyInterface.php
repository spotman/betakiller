<?php
declare(strict_types=1);

namespace BetaKiller\Assets\PathStrategy;

use BetaKiller\Assets\Model\AssetsModelInterface;

interface AssetsPathStrategyInterface
{
    /**
     * Find asset model by dispatchable part of url and return it
     *
     * @param string $path
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function getModelByPath(string $path): AssetsModelInterface;

    /**
     * Get dispatchable part of url for provided model
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param null|string                                   $delimiter
     *
     * @return string
     */
    public function makeModelPath(AssetsModelInterface $model, ?string $delimiter = null): string;
}
