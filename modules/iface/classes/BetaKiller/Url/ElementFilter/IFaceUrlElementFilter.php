<?php
declare(strict_types=1);

namespace BetaKiller\Url\ElementFilter;

use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;

class IFaceUrlElementFilter implements UrlElementFilterInterface
{
    /**
     * Checking availability of IFace URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return bool
     */
    public function isAvailable(UrlElementInterface $urlElement): bool
    {
        return $urlElement instanceof IFaceModelInterface;
    }
}
