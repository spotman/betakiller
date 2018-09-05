<?php
declare(strict_types=1);

namespace BetaKiller\Task\Geo\Providers;

use BetaKiller\Config\GeoMaxMindConfig;
use BetaKiller\Config\GeoMaxMindDownloadConfigInterface;
use BetaKiller\Log\Logger;
use BetaKiller\Model\Language;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;

abstract class AbstractImportCountriesCitiesMaxMind extends AbstractTask
{
    protected const TEMPLATE_FILE_NAME     = '';
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
     * @var \BetaKiller\Config\GeoMaxMindDownloadConfigInterface
     */
    private $downloadConfig;

    /**
     * @var \BetaKiller\Repository\LanguageRepository
     */
    private $languageRepository;

    /**
     * @var \BetaKiller\Log\Logger
     */
    protected $logger;

    /**
     * @param \BetaKiller\Config\GeoMaxMindConfig                  $config
     * @param \BetaKiller\Config\GeoMaxMindDownloadConfigInterface $downloadConfig
     * @param \BetaKiller\Repository\LanguageRepository            $languageRepository
     * @param \BetaKiller\Log\Logger                               $logger
     */
    public function __construct(
        GeoMaxMindConfig $config,
        GeoMaxMindDownloadConfigInterface $downloadConfig,
        LanguageRepository $languageRepository,
        Logger $logger
    ) {
        $this->config             = $config;
        $this->downloadConfig     = $downloadConfig;
        $this->languageRepository = $languageRepository;
        $this->logger             = $logger;

        parent::__construct();
    }

    public function run(): void
    {
        $tempPath = $this->createTempPath();
        $this->logger->debug('Temp path: '.$tempPath);

        $downloadUrl  = $this->downloadConfig->getPathDownloadUrlCsv();
        $csvFilesPath = $this->download($tempPath, $downloadUrl);

        $languages = $this->getLanguages();

        $languagesAliases = $this->config->getLanguagesAliasesLocales();
        foreach ($languages as $languageModel) {
            $languageLocale        = $languageModel->getLocale();
            $csvFileLanguageLocale = $this->getCsvFileLanguageLocale($languageLocale, $languagesAliases);
            $csvFilePath           = $this->createFilePath(
                $csvFilesPath,
                static::TEMPLATE_FILE_NAME,
                $csvFileLanguageLocale
            );
            try {
                $this->parseCsvAndImport($csvFilePath, $languageModel);
            } catch (\Exception $exception) {
                throw new TaskException(
                    'Unable parse and import: :error. File: :csvFilePath', [
                    ':error'       => $exception->getMessage(),
                    ':csvFilePath' => $csvFilePath,
                ]);
            }
        }

        $this->logger->debug('Removing: '.$tempPath);
        $this->runShellCommand(
            self::SHELL_COMMAND_REMOVE, [
            'path' => $tempPath,
        ]);

//        var_dump(memory_get_peak_usage());
//        var_dump(memory_get_peak_usage(true));
    }

    /**
     * @return array|\BetaKiller\Model\Language[]
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function getLanguages(): array
    {
        /**
         * @var \BetaKiller\Model\Language[] $languages
         * @var \BetaKiller\Model\Language[] $languagesByIndex
         */
        $languages        = [];
        $languagesByIndex = $this->languageRepository->getAll();
        foreach ($languagesByIndex as $languageModel) {
            $languages[$languageModel->getLocale()] = $languageModel;
        }

        $languageItemsLocale = $this->config->getLanguageItemsLocale();

        if (!isset($languages[$languageItemsLocale])) {
            $languagesAvailable = array_keys($languages);
            $languagesAvailable = implode(',', $languagesAvailable);
            throw new TaskException(
                'Not found language items locale ":search" in languages locales: :available', [
                ':search'    => $languageItemsLocale,
                ':available' => $languagesAvailable,
            ]);
        }

        $languageFirstModel = $languages[$languageItemsLocale];
        unset($languages[$languageItemsLocale]);
        $languages = [$languageItemsLocale => $languageFirstModel] + $languages;

        return $languages;
    }

    /**
     * @param string   $languageLocale
     * @param string[] $languagesAliases
     *
     * @return string
     * @throws \BetaKiller\Task\TaskException
     */
    private function getCsvFileLanguageLocale(string $languageLocale, array $languagesAliases): string
    {
        if (!isset($languagesAliases[$languageLocale])) {
            $languagesAvailable = array_keys($languagesAliases);
            $languagesAvailable = implode(',', $languagesAvailable);
            throw new TaskException(
                'Not found language locale ":search" in languages aliases: :available', [
                ':search'    => $languageLocale,
                ':available' => $languagesAvailable,
            ]);
        }

        return (string)$languagesAliases[$languageLocale];
    }

    /**
     * @param string                     $csvFilePath
     * @param \BetaKiller\Model\Language $languageModel
     *
     * @return \BetaKiller\Task\Geo\Providers\AbstractImportCountriesCitiesMaxMind
     * @throws \BetaKiller\Task\TaskException
     */
    private function parseCsvAndImport(string $csvFilePath, Language $languageModel): self
    {
        $handle = fopen($csvFilePath, 'rb');
        if ($handle === '') {
            throw new TaskException('Unable open file: '.$csvFilePath);
        }
        $lineIndex = 0;
        while (true) {
            $csvLine = fgetcsv($handle, 1000, static::CSV_SEPARATOR);
            if ($csvLine === false) {
                break;
            }
            $lineIndex++;
            if ($lineIndex > 1 && $lineIndex < 10) {
                $this->import($csvLine, $languageModel);
            }
        }
        fclose($handle);

        return $this;
    }

    /**
     * @param array                      $csvLine
     * @param \BetaKiller\Model\Language $languageModel
     *
     * @return void
     */
    abstract protected function import(array $csvLine, Language $languageModel): void;

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
     * @return \BetaKiller\Task\Geo\Providers\AbstractImportCountriesCitiesMaxMind
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
                'Execute shell command error. Command: :command. Result code: :code', [
                ':command' => $command,
                ':code'    => $result,
            ]);
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
     * @return \BetaKiller\Task\Geo\Providers\AbstractImportCountriesCitiesMaxMind
     * @throws \BetaKiller\Task\TaskException
     */
    protected function removeDirectory(string $path): self
    {
        if (file_exists($path) && !rmdir($path)) {
            throw new TaskException('Unable remove directory: '.$path);
        }

        return $this;
    }

    /**
     * @param string $path
     *
     * @return \BetaKiller\Task\Geo\Providers\AbstractImportCountriesCitiesMaxMind
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
            'Unable create unique path in directory: :parentPath. Limit attempts: :attempts', [
            ':parentPath' => $parentPath,
            ':attempts'   => self::PATH_TEMP_CREATING_ATTEMPTS,
        ]);
    }
}

