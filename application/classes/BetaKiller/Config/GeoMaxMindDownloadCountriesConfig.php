<?php
declare(strict_types=1);

namespace BetaKiller\Config;

class GeoMaxMindDownloadCountriesConfig extends AbstractConfig implements GeoMaxMindDownloadConfigInterface
{
    public const PATH_DOWNLOAD_URL_BIN = ['maxmind', 'downloadUrls', 'countries', 'bin'];
    public const PATH_DOWNLOAD_URL_CSV = ['maxmind', 'downloadUrls', 'countries', 'csv'];

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
    public function getPathDownloadUrlCsv(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_CSV);
    }

    /**
     * @return string
     */
    public function getPathDownloadUrlBin(): string
    {
        return (string)$this->get(self::PATH_DOWNLOAD_URL_BIN);
    }
}
