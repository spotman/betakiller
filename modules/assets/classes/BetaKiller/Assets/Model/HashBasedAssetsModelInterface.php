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
     * Returns unique hash for provided content
     *
     * @param string $content
     *
     * @return string
     */
    public function setHashFromContent(string $content): string;
}
