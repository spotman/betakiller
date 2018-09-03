<?php
declare(strict_types=1);

namespace BetaKiller\Task\Geo;

use BetaKiller\Config\GeoMaxMindConfig;
use BetaKiller\Log\Logger;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;

class ImportCountriesAndCities extends AbstractTask
{
    //todo alias for locale code in file name
    private const PATH_TEMP_CREATING_ATTEMPTS = 20;
    private const LOCALE_MAIN                 = 'en';
    private const TEMPLATE_FILE_COUNTRY       = 'GeoLite2-Country-Locations-:locale.csv';
    private const TEMPLATE_FILE_CITY          = 'GeoLite2-City-Locations-:locale.csv';
    private const CSV_SEPARATOR               = ',';
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
        // todo separate reading CSV by files for memory limit control
        // todo synchronization between files
        $countries = $this->downloadCountries();
        $cities    = $this->downloadCities();

        var_dump(memory_get_peak_usage());
        var_dump(memory_get_peak_usage(true));
    }

    /**
     * @return array
     * @throws \BetaKiller\Task\TaskException
     */
    public function downloadCountries(): array
    {
        $downloadUrl = $this->config->getPathDownloadUrlCountriesCsv();
        $filesPath   = $this->download($downloadUrl);

        // todo convert to [[id,code,isEu,name:[de,en,..]],..]
        $items   = [];
        $locales = $this->config->getLocales();
        foreach ($locales as $locale) {
            $csvFilePath = $this->createFilePath($filesPath, self::TEMPLATE_FILE_COUNTRY, $locale);
            $this->testFileExists($csvFilePath);

            // todo remove duplicates by code
            $this->logger->debug('Parsing path: '.$csvFilePath);
            $itemsForLocale = $this->readCsvFile($csvFilePath);
            foreach ($itemsForLocale as &$item) {
                $item = [
                    'id'   => $item[0],
                    'code' => strtolower($item[2]),
                    'name' => $item[3],
                    'isEu' => (bool)$item[6],
                ];
            }
            unset($item);

            $items[$locale] = $itemsForLocale;
        }

        return $items;
    }

    /**
     * @return array
     * @throws \BetaKiller\Task\TaskException
     */
    public function downloadCities(): array
    {
        $downloadUrl = $this->config->getPathDownloadUrlCitiesCsv();
        $filesPath   = $this->download($downloadUrl);

        // todo convert to [countryCode:[id,name:[de,en,..]],..]
        $items   = [];
        $locales = $this->config->getLocales();
        foreach ($locales as $locale) {
            $csvFilePath = $this->createFilePath($filesPath, self::TEMPLATE_FILE_CITY, $locale);
            $this->testFileExists($csvFilePath);

            if (!isset($items[$locale])) {
                $items[$locale] = [];
            }

            // todo remove duplicates by name
            $this->logger->debug('Parsing path: '.$csvFilePath);
            $itemsForLocale = $this->readCsvFile($csvFilePath);
            foreach ($itemsForLocale as $item) {
                $countryCode = strtolower($item[4]);
                if (!isset($items[$locale][$countryCode])) {
                    $items[$locale][$countryCode] = [];
                }
                $items[$locale][$countryCode][] = [
                    'id'   => $item[0],
                    'name' => $item[5],
                ];
            }
        }

        return $items;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    private function readCsvFile(string $path): array
    {
        $lines = str_getcsv(file_get_contents($path), "\n");
        foreach ($lines as &$line) {
            $line = str_getcsv($line, self::CSV_SEPARATOR);
        }
        unset($line);

        if (\count($lines) === 1 && isset($lines[0]) && $lines[0] === null) {
            throw new TaskException('Unable parsing CSV file: '.$path);
        }

        return $lines;
    }

    /**
     * @param string $path
     *
     * @return \BetaKiller\Task\Geo\ImportCountriesAndCities
     * @throws \BetaKiller\Task\TaskException
     */
    private function testFileExists(string $path): self
    {
        if (!file_exists($path)) {
            throw new TaskException('Not found file: '.$path);
        }

        return $this;
    }

    /**
     * @param string $directory
     * @param string $fileNameTemplate
     * @param string $localeCode
     *
     * @return string
     */
    private function createFilePath(string $directory, string $fileNameTemplate, string $localeCode): string
    {
        $fileName = $this->fillString($fileNameTemplate, ['locale' => $localeCode]);

        return $directory.DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * @param string $downloadUrl
     *
     * @return string
     * @throws \BetaKiller\Task\TaskException
     */
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
}

