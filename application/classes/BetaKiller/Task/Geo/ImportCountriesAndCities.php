<?php
declare(strict_types=1);

namespace BetaKiller\Task\Geo;

use BetaKiller\Config\GeoMaxMindConfig;
use BetaKiller\Log\Logger;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;

class ImportCountriesAndCities extends AbstractTask
{
    private const PATH_TEMP_CREATING_ATTEMPTS = 20;
    private const SHELL_COMMAND_REMOVE        = 'rm -rf :path';
    private const SHELL_COMMAND_DOWNLOAD      = 'wget :downloadUrl -O :resultPath';
    private const SHELL_COMMAND_UNZIP         = 'unzip -o :zipPath -d :resultPath';

    /**
     * @var \BetaKiller\Config\GeoMaxMindConfig
     */
    private $config;

    /**
     * @var \BetaKiller\Log\Logger
     */
    private $logger;

    /**
     * ImportCountriesAndCities constructor.
     *
     * @param \BetaKiller\Config\GeoMaxMindConfig $config
     * @param \BetaKiller\Log\Logger              $logger
     */
    public function __construct(GeoMaxMindConfig $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;

        parent::__construct();
    }

    public function run(): void
    {
        $downloadUrl   = $this->config->getPathDownloadUrlCountriesCsv();
        $countriesPath = $this->download($downloadUrl);

        $downloadUrl = $this->config->getPathDownloadUrlCitiesCsv();
        $citiesPath  = $this->download($downloadUrl);

        var_dump($countriesPath);
        var_dump($citiesPath);
    }

    private function download(string $downloadUrl): string
    {
        $tempPath = $this->createTempPath();
        $this->logger->debug('Temp path: '.$tempPath);

        $this->createDirectory($tempPath);

        $this->logger->debug('Downloading url: '.$downloadUrl);

        $zipPath = explode('/', $downloadUrl);
        $zipPath = $tempPath.DIRECTORY_SEPARATOR.array_pop($zipPath);
        $this->logger->debug('Zip path: '.$zipPath);

        $this->runCommand(
            self::SHELL_COMMAND_DOWNLOAD, [
            'downloadUrl' => $downloadUrl,
            'resultPath'  => $zipPath,
        ]);

        $this->runCommand(
            self::SHELL_COMMAND_UNZIP, [
            'zipPath'    => $zipPath,
            'resultPath' => $tempPath,
        ]);

        $this->runCommand(
            self::SHELL_COMMAND_REMOVE, [
            'path' => $zipPath,
        ]);

        return $this->findFirstDirectoryPath($tempPath);
    }

    /**
     * @param string $searchPath
     *
     * @return string
     * @throws \BetaKiller\Task\TaskException
     */
    private function findFirstDirectoryPath(string $searchPath): string
    {
        $paths = glob($searchPath.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
        if (empty($paths)) {
            throw new TaskException('Unable find first directory in: '.$searchPath);
        }

        return $paths[0];
    }

    /**
     * @param string $command
     * @param array  $data
     *
     * @return \BetaKiller\Task\Geo\ImportCountriesAndCities
     * @throws \BetaKiller\Task\TaskException
     */
    private function runCommand(string $command, array $data): self
    {
        $command = $this->fillString($command, $data);
        $this->logger->debug('Run command: '.$command);

        $output = null;
        $result = null;
        exec($command, $output, $result);
        if (!is_numeric($result) || $result > 0) {
            throw new TaskException(
                sprintf(
                    'Execute shell command error. Command: %s. Result code: %s',
                    $command, $result
                )
            );
        }

        return $this;
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @return string
     */
    private function fillString(string $template, array $data): string
    {
        if (!$data) {
            return $template;
        }

        $search = array_keys($data);
        array_walk($search, function (&$value) {
            $value = ':'.$value;
        });
        $replace = array_values($data);

        return str_replace($search, $replace, $template);
    }

    /**
     * @param string $path
     *
     * @return \BetaKiller\Task\Geo\ImportCountriesAndCities
     * @throws \BetaKiller\Task\TaskException
     */
    private function removeDirectory(string $path): self
    {
        if (file_exists($path)) {
            if (!rmdir($path)) {
                throw new TaskException('Unable remove directory: '.$path);
            }
        }

        return $this;
    }

    /**
     * @param string $path
     *
     * @return \BetaKiller\Task\Geo\ImportCountriesAndCities
     * @throws \BetaKiller\Task\TaskException
     */
    private function createDirectory(string $path): self
    {
        if (!file_exists($path)) {
            if (!mkdir($path) || !is_dir($path)) {
                throw new TaskException('Unable create directory: '.$path);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    private function createTempPath(): string
    {
        $prefix     = \hash('md5', __CLASS__.microtime(true));
        $parentPath = sys_get_temp_dir();
        for ($i = 0; $i < self::PATH_TEMP_CREATING_ATTEMPTS; $i++) {
            $path = $parentPath.DIRECTORY_SEPARATOR.\uniqid($prefix, true);
            if (!\file_exists($path)) {
                return $path;
            }
            $this->logger->debug(
                sprintf(
                    'Creating unique path. Path exists: %s. Repeat %s/%s',
                    $path, $i, self::PATH_TEMP_CREATING_ATTEMPTS
                )
            );
        }

        throw new TaskException(
            sprintf(
                'Unable create unique path in directory: %s. Limit attempts: %s',
                $parentPath, self::PATH_TEMP_CREATING_ATTEMPTS
            )
        );
    }


    public function run2(): void
    {
        $tempPath = \sys_get_temp_dir().DIRECTORY_SEPARATOR.\uniqid(\hash('md5', __CLASS__), true);
        echo PHP_EOL;
        echo $tempPath;
        if (file_exists($tempPath)) {
            rmdir($tempPath);
        }

        echo PHP_EOL;
        if (!\mkdir($tempPath) && !\is_dir($tempPath)) {
            echo 0;
        } else {
            echo 1;
        }

        echo PHP_EOL;
        $tempFilePath = tempnam($tempPath, \hash('md5', $tempPath));
        if (\file_exists($tempFilePath)) {
            echo 1;
        } else {
            echo 0;
        }

        echo PHP_EOL;
        rmdir($tempPath);
        if (file_exists($tempPath)) {
            echo 1;
        } else {
            echo 0;
        }

        exit;

        $downloadCommand = '
            rm -rf /var/www/spa.dev/www/GeoTemp;
            wget http://geolite.maxmind.com/download/geoip/database/GeoLite2-City-CSV.zip -O /var/www/spa.dev/www/GeoTemp.zip;
            unzip -o /var/www/spa.dev/www/GeoTemp.zip -d /var/www/spa.dev/www/GeoTemp;
            rm -rf /var/www/spa.dev/www/GeoTemp.zip
        ';
        shell_exec($downloadCommand);

        $downloadPath = '/var/www/spa.dev/www/GeoTemp';
        if (!\file_exists($downloadPath)) {
            echo 'Not found';
        } else {
            echo 'ok';
        }
    }
}

