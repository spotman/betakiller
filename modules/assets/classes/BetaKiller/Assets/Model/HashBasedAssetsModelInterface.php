<?php
namespace BetaKiller\Assets\Model;

/**
 * Interface HashBasedAssetsModelInterface
 *
 * Abstract model interface for asset file
 */
interface HashBasedAssetsModelInterface
{
    /**
     * Returns unique hash
     *
     * @return string|null
     */
    public function getHash(): ?string;

    /**
     * Stores unique hash
     *
     * @param string $hash
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function setHash(string $hash): AssetsModelInterface;
}
