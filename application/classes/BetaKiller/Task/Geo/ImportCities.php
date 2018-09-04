<?php
declare(strict_types=1);

namespace BetaKiller\Task\Geo;

use BetaKiller\Model\Language;

class ImportCities extends AbstractImport
{
    protected const TEMPLATE_FILE_NAME = 'GeoLite2-City-Locations-:language.csv';

    /**
     * @param array                      $items
     * @param \BetaKiller\Model\Language $languageApp
     *
     * @return void
     */
    protected function import(array $items, Language $languageApp): void
    {

    }

    /**
     * @param string $csvFilePath
     *
     * @return array
     * @throws \BetaKiller\Task\TaskException
     */
    protected function parseCsv(string $csvFilePath): array
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
}

