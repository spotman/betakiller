<?php
namespace BetaKiller\IFace\Url;

/**
 * Interface NonPersistentUrlParameterInterface
 *
 * @package BetaKiller\IFace\Url
 */
interface NonPersistentUrlParameterInterface extends UrlParameterInterface
{
    /**
     * Non-persistent url parameters needs codename to be defined
     *
     * @return string
     */
    public static function getCodename(): string;
}
