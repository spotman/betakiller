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
     * Assigns unique hash
     *
     * @param string $hash
     *
     * @return void
     */
    public function setHash(string $hash): void;
}
