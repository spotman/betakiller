<?php
declare(strict_types=1);

namespace BetaKiller\Task\Geo;

use BetaKiller\Config\GeoMaxMindConfig;
use BetaKiller\Log\Logger;
use BetaKiller\Model\City;
use BetaKiller\Model\Country;
use BetaKiller\Model\Language;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;

abstract class AbstractImport extends AbstractTask
{
    protected const TEMPLATE_FILE_NAME     = '';
    protected const LANGUAGE_NAME_MAIN     = 'en';
    protected const CSV_SEPARATOR          = ',';
    protected const SHELL_COMMAND_REMOVE   = 'rm -rf :path';
    protected const SHELL_COMMAND_DOWNLOAD = 'wget :downloadUrl -O :resultPath';
    protected const SHELL_COMMAND_UNZIP    = 'unzip -o :zipPath -d :resultPath';

    private const PATH_TEMP_CREATING_ATTEMPTS = 10;

    /**
     * @var \BetaKiller\Config\GeoMaxMindConfig
     */
    protected $config;

    /**
     * @var \BetaKiller\Log\Logger
     */
    protected $logger;

    /**
     * @Inject
     * @var \BetaKiller\Repository\CityRepository
     */
    protected $countriesRepository;

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
        $tempPath = $this->createTempPath();
        $this->logger->debug('Temp path: '.$tempPath);

        $downloadUrl  = $this->config->getPathDownloadUrlCountriesCsv();
        $csvFilesPath = $this->download($tempPath, $downloadUrl);

        $languagesApp   = (new Language())->get_all();
        $languagesFiles = $this->config->getLanguages();

        foreach ($languagesApp as $languageApp) {
            $languageAppLocale  = $languageApp->getLocale();
            $languageFileLocale = $languagesFiles[$languageAppLocale];

            $csvFilePath = $this->createFilePath($csvFilesPath, self::TEMPLATE_FILE_NAME, $languageFileLocale);
            $items       = $this->parseCsv($csvFilePath);

            $this->import($items, $languageApp);
        }

        $this->runShellCommand(
            self::SHELL_COMMAND_REMOVE, [
            'path' => $tempPath,
        ]);

        var_dump(memory_get_peak_usage());
        var_dump(memory_get_peak_usage(true));
    }

    /**
     * @param array                      $items
     * @param \BetaKiller\Model\Language $languageApp
     *
     * @return void
     */
    abstract protected function import(array $items, Language $languageApp): void;

    /**
     * @param string $csvFilePath
     *
     * @return array
     * @throws \BetaKiller\Task\TaskException
     */
    abstract protected function parseCsv(string $csvFilePath): array;

    /**
     * @param string $tempPath
     * @param string $downloadUrl
     *
     * @return string
     * @throws \BetaKiller\Task\TaskException
     */
    protected function download(string $tempPath, string $downloadUrl): string
    {
        $this->createDirectory($tempPath);

        $this->logger->debug('Downloading url: '.$downloadUrl);

        $zipPath = explode('/', $downloadUrl);
        $zipPath = $tempPath.DIRECTORY_SEPARATOR.array_pop($zipPath);
        $this->logger->debug('Zip path: '.$zipPath);

        $this->runShellCommand(
            self::SHELL_COMMAND_DOWNLOAD, [
            'downloadUrl' => $downloadUrl,
            'resultPath'  => $zipPath,
        ]);

        $this->runShellCommand(
            self::SHELL_COMMAND_UNZIP, [
            'zipPath'    => $zipPath,
            'resultPath' => $tempPath,
        ]);

        return $this->findFirstDirectoryPath($tempPath);
    }

    /**
     * @param string $command
     * @param array  $data
     *
     * @return \BetaKiller\Task\Geo\AbstractImport
     * @throws \BetaKiller\Task\TaskException
     */
    protected function runShellCommand(string $command, array $data): self
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
     * @param string $path
     * @param string $separator
     *
     * @return array
     */
    protected function readCsvFile(string $path, string $separator): array
    {
        if (!file_exists($path)) {
            throw new TaskException('Not found file: '.$path);
        }

        $lines = str_getcsv(file_get_contents($path), "\n");
        foreach ($lines as &$line) {
            $line = str_getcsv($line, $separator);
        }
        unset($line);

        if (\count($lines) === 1 && isset($lines[0]) && $lines[0] === null) {
            throw new TaskException('Unable parse CSV: '.$path);
        }

        return $lines;
    }

    /**
     * @param string $directory
     * @param string $fileNameTemplate
     * @param string $languageCode
     *
     * @return string
     */
    protected function createFilePath(string $directory, string $fileNameTemplate, string $languageCode): string
    {
        $fileName = $this->fillString($fileNameTemplate, ['language' => $languageCode]);

        return $directory.DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * @param string $searchPath
     *
     * @return string
     * @throws \BetaKiller\Task\TaskException
     */
    protected function findFirstDirectoryPath(string $searchPath): string
    {
        $paths = glob($searchPath.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
        if (empty($paths)) {
            throw new TaskException('Unable find first directory in: '.$searchPath);
        }

        return $paths[0];
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @return string
     */
    protected function fillString(string $template, array $data): string
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
     * @return \BetaKiller\Task\Geo\AbstractImport
     * @throws \BetaKiller\Task\TaskException
     */
    protected function removeDirectory(string $path): self
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
     * @return \BetaKiller\Task\Geo\AbstractImport
     * @throws \BetaKiller\Task\TaskException
     */
    protected function createDirectory(string $path): self
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
    protected function createTempPath(): string
    {
        $prefix     = \hash('md5', __CLASS__.microtime(true));
        $parentPath = sys_get_temp_dir();
        for ($i = 0; $i < self::PATH_TEMP_CREATING_ATTEMPTS; $i++) {
            $path = $parentPath.DIRECTORY_SEPARATOR.\uniqid($prefix, true);
            if (!\file_exists($path)) {
                return $path;
            }
        }

        throw new TaskException(
            sprintf(
                'Unable create unique path in directory: %s. Limit attempts: %s',
                $parentPath, self::PATH_TEMP_CREATING_ATTEMPTS
            )
        );
    }
}

