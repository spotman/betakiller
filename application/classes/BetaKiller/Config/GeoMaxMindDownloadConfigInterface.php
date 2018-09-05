<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface GeoMaxMindDownloadConfigInterface
{
    public const CONFIG_GROUP_NAME = 'geo';

    /**
     * @return string
     */
    public function getPathDownloadUrlCsv(): string;

    /**
     * @return string
     */
    public function getPathDownloadUrlBin(): string;
}
