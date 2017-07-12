<?php
namespace BetaKiller\IFace\Url;

/**
 * Interface ConfigBasedUrlParameterInterface
 *
 * @package BetaKiller\Core
 */
interface ConfigBasedUrlParameterInterface extends UrlParameterInterface
{
    /**
     * Config-based url parameters needs codename to be defined
     *
     * @return string
     */
    public function getCodename(): string;
}
