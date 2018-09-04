<?php
declare(strict_types=1);

namespace BetaKiller\Task\Geo;

use BetaKiller\Model\Language;

class ImportCountriesAndCities extends AbstractImport
{
    private const TEMPLATE_FILE_NAME_COUNTRY = 'GeoLite2-Country-Locations-:language.csv';
    private const TEMPLATE_FILE_NAME_CITY    = 'GeoLite2-City-Locations-:language.csv';
    private const CSV_SEPARATOR              = ',';
    private const SHELL_COMMAND_REMOVE       = 'rm -rf :path';
    private const SHELL_COMMAND_DOWNLOAD     = 'wget :downloadUrl -O :resultPath';
    private const SHELL_COMMAND_UNZIP        = 'unzip -o :zipPath -d :resultPath';

    public function run(): void
    {
        $languagesApp   = (new Language())->get_all();
        $languagesFiles = $this->config->getLanguages();

        foreach ($languagesApp as $languageApp) {
            $languageAppLocale  = $languageApp->getLocale();
            $languageFileLocale = $languagesFiles[$languageAppLocale];

            $downloadUrl  = $this->config->getPathDownloadUrlCountriesCsv();
            $csvFilesPath = $this->download($downloadUrl);
            $csvFilePath  = $this->createFilePath($csvFilesPath, self::TEMPLATE_FILE_NAME_COUNTRY, $languageFileLocale);
            $countries    = $this->parseCountries($csvFilePath);

            //
            $downloadUrl  = $this->config->getPathDownloadUrlCitiesCsv();
            $csvFilesPath = $this->download($downloadUrl);
            $csvFilePath  = $this->createFilePath($csvFilesPath, self::TEMPLATE_FILE_NAME_CITY, $languageFileLocale);
            $cities    = $this->parseCities($csvFilePath);
        }

        var_dump(memory_get_peak_usage());
        var_dump(memory_get_peak_usage(true));
    }

    /**
     * @param string $csvFilePath
     *
     * @return array
     * @throws \BetaKiller\Task\TaskException
     */
    public function parseCountries(string $csvFilePath): array
    {
        $this->logger->debug('Parsing CSV: '.$csvFilePath);

        $items = [];
        $lines = $this->readCsvFile($csvFilePath, self::CSV_SEPARATOR);
        foreach ($lines as $line) {
            $code         = strtolower($line[2]);
            $items[$code] = [
                'id'   => $line[0],
                'code' => $code,
                'name' => $line[3],
                'isEu' => (bool)$line[6],
            ];
        }

        return array_values($items);
    }

    /**
     * @param string $csvFilePath
     *
     * @return array
     * @throws \BetaKiller\Task\TaskException
     */
    public function parseCities(string $csvFilePath): array
    {
        $this->logger->debug('Parsing CSV: '.$csvFilePath);

        $items = [];
        $lines = $this->readCsvFile($csvFilePath, self::CSV_SEPARATOR);
        foreach ($lines as $line) {
            $id         = $line[0];
            $items[$id] = [
                'id'   => $id,
                'name' => $line[5],
            ];
        }

        return array_values($items);
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

        return $this->findFirstDirectoryPath($tempPath);
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
        $this->runShellCommand($command);

        return $this;
    }
}

