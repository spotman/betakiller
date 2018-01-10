<?php
namespace BetaKiller\Url;

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
     * @param \BetaKiller\Url\UrlParameterInterface $parameter
     *
     * @return bool
     */
    public function isSameAs(UrlParameterInterface $parameter): bool;
}
