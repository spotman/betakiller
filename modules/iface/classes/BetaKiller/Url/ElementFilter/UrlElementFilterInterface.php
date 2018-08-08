<?php
declare(strict_types=1);

namespace BetaKiller\Url\ElementFilter;

use BetaKiller\Url\UrlElementInterface;

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
