<?php
namespace BetaKiller\Url;

/**
 * Filter of IFace URL element
 */
interface UrlElementFilterInterface
{
    /**
     * Checking availability of IFace URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return bool
     */
    public function isAvailable(UrlElementInterface $urlElement): bool;
}
