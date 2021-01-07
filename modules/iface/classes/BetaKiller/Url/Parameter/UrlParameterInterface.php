<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

interface UrlParameterInterface
{
    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string;

    /**
     * Returns true if current parameter is the same as provided one
     *
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface $parameter
     *
     * @return bool
     */
    public function isSameAs(UrlParameterInterface $parameter): bool;

    /**
     * Must return true if caching of this UrlParameter is allowed
     *
     * @return bool
     */
    public function isCachingAllowed(): bool;
}
