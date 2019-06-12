<?php
namespace BetaKiller\Url;

interface UrlElementInstanceInterface
{
    /**
     * @return string
     */
    public static function codename(): string;

    /**
     * @return string
     */
    public static function getSuffix(): string;
}
