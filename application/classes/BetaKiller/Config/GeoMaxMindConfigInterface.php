<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface GeoMaxMindConfigInterface
{
    public const CONFIG_GROUP_NAME             = 'geo';
    public const PATH_DOWNLOAD_URL_CITY_CSV    = ['maxmind', 'downloadUrls', 'city', 'csv'];
    public const PATH_DOWNLOAD_URL_CITY_BINARY = ['maxmind', 'downloadUrls', 'city', 'binary'];

    /**
     * @return string
     */
    public function getPathDownloadUrlCityCsv(): string;

    /**
     * @return string
     */
    public function getPathDownloadUrlCityBinary(): string;
}
