<?php
declare(strict_types=1);

namespace BetaKiller\Config;

class GeoMaxMindConfig extends AbstractConfig implements GeoMaxMindConfigInterface
{
    /**
     * @return string
     */
    protected function getConfigRootGroup(): string
    {
        return self::CONFIG_GROUP_NAME;
    }

    /**
     * @return string
     */
    public function getPathDownloadUrlCountryCsv(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_COUNTRY_CSV);
    }

    /**
     * @return string
     */
    public function getPathDownloadUrlCountryBinary(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_COUNTRY_BINARY);
    }

    /**
     * @return string
     */
    public function getPathDownloadUrlCityCsv(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_CITY_CSV);
    }

    /**
     * @return string
     */
    public function getPathDownloadUrlCityBinary(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_CITY_BINARY);
    }
}
