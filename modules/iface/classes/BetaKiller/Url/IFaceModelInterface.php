<?php

namespace BetaKiller\Url;

use BetaKiller\Helper\SeoMetaInterface;
use DateInterval;

interface IFaceModelInterface extends EntityLinkedUrlElementInterface, SeoMetaInterface, UrlElementForMenuInterface,
                                      UrlElementWithLayoutInterface
{
    public const OPTION_CACHE = 'cache';

    /**
     * Returns true if HTTP caching is enabled for this IFace
     *
     * @return bool
     */
    public function isCacheEnabled(): bool;

    /**
     * Returns date interval for HTTP caching "expires" header
     *
     * @return DateInterval|null
     */
    public function getExpiresInterval(): ?DateInterval;
}
