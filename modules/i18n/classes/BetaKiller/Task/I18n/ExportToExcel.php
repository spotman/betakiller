<?php
declare(strict_types=1);

namespace BetaKiller\Task\I18n;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Task\AbstractTask;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class ExportToExcel extends AbstractTask
{
    private const CELL_KEY = 'key';
    private const CELL_EN  = 'en';
    private const CELL_DE  = 'de';

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * ExportToExcel constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade     $i18n
     * @param \BetaKiller\Env\AppEnvInterface $appEnv
     */
    public function __construct(I18nFacade $i18n, AppEnvInterface $appEnv)
    {
        parent::__construct();

        $this->appEnv = $appEnv;
        $this->i18n   = $i18n;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        // Generate sheet
        $spreadsheet = $this->createSpreadSheet();

        // Store sheet to a temp file
        $fileName = $this->appEnv->getStoragePath('i18n_keys.xlsx');
        $writer   = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($fileName);

        // Cleanup memory
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    private function createSpreadSheet(): Spreadsheet
    {
        $spread = new Spreadsheet();

        $spread->getProperties()
            ->setCreator('Website')
            ->setModified(time())
            ->setLastModifiedBy('Website')
            ->setTitle('Localization keys export')
            ->setSubject('Localization keys export');

        // Create worksheet
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('I18n keys');

        $rowIndex = 1;

        $structure = $this->getStructure();

        $autoFilterStartRow = $rowIndex;

        // Add title row
        foreach (array_values($structure) as $columnIndex => $columnLabel) {
            $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex, $columnLabel);
        }
        $rowIndex++;

        // Add data
        foreach ($this->getData() as $row) {
            $columnIndex = 1;

            foreach ($structure as $columnName => $columnLabel) {
                $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $row[$columnName] ?? null);
                $columnIndex++;
            }
            $rowIndex++;
        }

        $sheet->setAutoFilterByColumnAndRow(
            1,
            $autoFilterStartRow,
            count($structure) + 1,
            $rowIndex
        );

        // Try to autosize columns
        foreach (array_keys($structure) as $columnIndex => $columnName) {
            $sheet->getColumnDimensionByColumn($columnIndex)->setAutoSize(true);
        }

        return $spread;
    }

    /**
     * @return \Generator|string[]
     */
    private function getData(): \Generator
    {
        $en = $this->i18n->getLanguageByIsoCode(LanguageInterface::ISO_EN);
        $de = $this->i18n->getLanguageByIsoCode(LanguageInterface::ISO_DE);

        foreach ($this->i18n->getAllTranslationKeys() as $key) {
            yield [
                self::CELL_KEY => $key->getI18nKeyName(),
                self::CELL_EN  => $key->hasI18nValue($en) ? $key->getI18nValue($en) : null,
                self::CELL_DE  => $key->hasI18nValue($de) ? $key->getI18nValue($de) : null,
            ];
        }
    }

    /**
     * @return string[]
     */
    private function getStructure(): array
    {
        return [
            self::CELL_KEY => 'Key',
            self::CELL_EN  => 'EN',
            self::CELL_DE  => 'DE',
        ];
    }
}
