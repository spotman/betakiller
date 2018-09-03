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
    public function getPathDownloadUrlCountriesCsv(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_COUNTRIES_CSV);
    }

    /**
     * @return string
     */
    public function getPathDownloadUrlCountriesBin(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_COUNTRIES_BIN);
    }

    /**
     * @return string
     */
    public function getPathDownloadUrlCitiesCsv(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_CITIES_CSV);
    }

    /**
     * @return string
     */
    public function getPathDownloadUrlCitiesBin(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_CITIES_BIN);
    }

    /**
     * @return array
     */
    public function getLocales(): array {
        return (string)$this->get(self::PATH_LOCALES);
    }
}
