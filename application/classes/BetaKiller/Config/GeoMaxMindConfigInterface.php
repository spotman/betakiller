<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface GeoMaxMindConfigInterface
{
    public const CONFIG_GROUP_NAME               = 'geo';
    public const PATH_DOWNLOAD_URL_COUNTRIES_CSV = ['maxmind', 'downloadUrls', 'countries', 'csv'];
    public const PATH_DOWNLOAD_URL_COUNTRIES_BIN = ['maxmind', 'downloadUrls', 'countries', 'binary'];
    public const PATH_DOWNLOAD_URL_CITIES_CSV    = ['maxmind', 'downloadUrls', 'cities', 'csv'];
    public const PATH_DOWNLOAD_URL_CITIES_BINARY = ['maxmind', 'downloadUrls', 'cities', 'binary'];

    /**
     * @return string
     */
    public function getPathDownloadUrlCountriesCsv(): string;

    /**
     * @return string
     */
    public function getPathDownloadUrlCountriesBinary(): string;

    /**
     * @return string
     */
    public function getPathDownloadUrlCitiesCsv(): string;

    /**
     * @return string
     */
    public function getPathDownloadUrlCitiesBinary(): string;
}
