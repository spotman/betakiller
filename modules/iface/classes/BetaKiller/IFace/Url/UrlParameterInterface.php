<?php
namespace BetaKiller\IFace\Url;

interface UrlParameterInterface
{
    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string;

    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     */
    public function getUrlKeyValue(string $key): string;

    /**
     * Returns true if current parameter is the same as provided one
     *
     * @param \BetaKiller\IFace\Url\UrlParameterInterface $parameter
     *
     * @return bool
     */
    public function isSameAs(UrlParameterInterface $parameter): bool;
}
