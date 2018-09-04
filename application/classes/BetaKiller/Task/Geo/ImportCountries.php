<?php
declare(strict_types=1);

namespace BetaKiller\Task\Geo;

use BetaKiller\Model\Language;

class ImportCountries extends AbstractImport
{
    private const TEMPLATE_FILE_NAME = 'GeoLite2-Country-Locations-:language.csv';

    public function run(): void
    {
        $languagesApp   = (new Language())->get_all();
        $languagesFiles = $this->config->getLanguages();

        $downloadUrl  = $this->config->getPathDownloadUrlCountriesCsv();
        $csvFilesPath = $this->download($downloadUrl);

        foreach ($languagesApp as $languageApp) {
            $languageAppLocale  = $languageApp->getLocale();
            $languageFileLocale = $languagesFiles[$languageAppLocale];

            $csvFilePath = $this->createFilePath($csvFilesPath, self::TEMPLATE_FILE_NAME, $languageFileLocale);
            $items       = $this->parseCsv($csvFilePath);
        }

        $this->runShellCommand(
            self::SHELL_COMMAND_REMOVE, [
            'path' => $csvFilesPath,
        ]);

        var_dump(memory_get_peak_usage());
        var_dump(memory_get_peak_usage(true));
    }

    /**
     * @param string $csvFilePath
     *
     * @return array
     * @throws \BetaKiller\Task\TaskException
     */
    public function parseCsv(string $csvFilePath): array
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
}

