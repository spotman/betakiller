<?php defined('SYSPATH') OR die('No direct script access.');

class Import_Excel_Doc {

    /** @var PHPExcel_Reader_IReader|PHPExcel_Reader_Excel5 */
    public $reader;

    /** @var PHPExcel */
    public $phpexcel;

    /** @var PHPExcel_Worksheet */
    public $active_excel_sheet;

    /** @var Import_Excel_Sheet */
    public $current_sheet;

    /** @var string формат даты, к которому приводятся ячейки с типом Дата */
    protected $date_format = "d.m.Y H:i:s";

    /**
     * путь к обрабатываему документу
     * @var string
     */
    private $filename;

    /**
     * массив объектов, описывающих листы в документе
     * @var Import_Excel_Sheet[]
     */
    private $sheet_list = array();

    /**
     * @param string $filename путь к обрабатываемому документу
     * @throws Import_Excel_Doc_Exception
     */
    public function __construct($filename)
    {
        if( !file_exists($filename) )
        {
            throw new Import_Excel_Doc_Exception("Ошибка при открытии документа [$filename] - файл не существует");
        }
        $this->filename = $filename;

//        $phpexcel_file = Kohana::find_file('vendor', 'phpoffice/phpexcel');
//
//        if( !$phpexcel_file )
//            throw new Import_Excel_Doc_Exception("Не могу найти библиотеку PHPExcel");
//
//        require $phpexcel_file;
    }

    /**
     * добавляет лист в очередь обработки
     * @param Import_Excel_Sheet $sheet
     * @return $this
     */
    public function use_sheet(Import_Excel_Sheet $sheet)
    {
        $sheet->set_doc($this);
        $sheet->init();
        $this->sheet_list[] = $sheet;
        return $this;
    }

    /**
     * выполняет обработку листов, находящихся в очереди
     * @throws Import_Excel_Doc_Exception
     */
    public function process()
    {
        // включаем сборщик мусора
        if ( ! gc_enabled() )
        {
            gc_enable();
        }

        // создаём правильный Reader на основании расширения файла
        $this->reader = PHPExcel_IOFactory::createReaderForFile($this->filename);

        if ( ! $this->reader )
            throw new Import_Excel_Doc_Exception("Невозможно подключить нужный PHPExcel_Reader");

        // включаем режим "только для чтения" (снижаем потребление памяти)
        // $this->reader->setReadDataOnly(true); - иначе не работает PHPExcel_Cell->getFormattedValue

        // выбираем только нужные листы (снижаем потребление памяти)
        $this->reader->setLoadSheetsOnly( $this->get_sheets_names() );

        // создаём объект из файла
        $this->phpexcel = $this->reader->load($this->filename);

        // освобождаем память
        unset($this->reader);

        // разрешаем проблему с циклическими ссылками между ячейками
        // http://stackoverflow.com/questions/10562388/exception-using-getcalculatedvalue-in-phpexcel
        PHPExcel_Calculation::getInstance()->cyclicFormulaCount = 1;

        // выполняем обработку каждого листа
        foreach ( $this->sheet_list as $sheet )
        {
            $this->current_sheet = $sheet;

            // индекс текущего листа
            $current_sheet_index = $this->get_sheet_index_by_name($sheet->get_sheet_name());

            $this->active_excel_sheet = $this->phpexcel->setActiveSheetIndex($current_sheet_index);
            $rowIterator = $this->active_excel_sheet->getRowIterator();

            $sheet_auto_format_cells = $sheet->get_auto_format_cells();

            // обходим каждую строку в документе
            foreach ( $rowIterator as $sheet_row )
            {
                /** @var PHPExcel_Worksheet_Row $sheet_row */

                $current_row_index = $sheet_row->getRowIndex();

                // если дошли до строки с заголовками столбцов, проверяем их
                if ( $current_row_index == $sheet->get_column_title_row() )
                {
                    // проверяем соответствие всех объявленных заголовков столбцов
                    foreach ( $sheet->get_column_structure() as $column => $title )
                    {
                        // получаем значение ячейки с заголовком
                        $value = $this->get_cell_value($column, $current_row_index, false); // no auto_format
                        if( $value != $title )
                            throw new Import_Excel_Doc_Exception('Формат загруженного документа не соответствует образцу: в заголовке столбца ['. $column .'] ожидается строка "'. $title .'", а в документе "'. $value .'"');
                    }

                    // всё хорошо - переходим к импорту
                    continue;
                }

                // пропускаем строки в шапке
                if ( $current_row_index <= $sheet->get_header_size() )
                    continue;

                /** @var array $row_data массив значений ячеек в текущей строке */
                $row_data = array();

                // выбираем данные для ячеек, описанных в структуре листа
                foreach ( $sheet->get_column_structure() as $column => $title )
                {
                    // Определяем нужно ли производить автоформат текущей ячейки
                    $auto_format = ( $sheet_auto_format_cells AND in_array($column, $sheet_auto_format_cells) );

                    $cell_value = $this->get_cell_value($column, $current_row_index, $auto_format);
                    // if( !$cell_value ) continue;        // пропускаем пустые поля
                    $row_data[ $column ] = $cell_value;
                }

                // пропускаем пустые строки
                if ( count($row_data) == 0 ) continue;

                $result = FALSE;

                // вызвываем обработчик строки
                try
                {
                    // передаём данные обработчику строки
                    $result = $sheet->process_row($row_data);
                }
                catch(Import_Excel_Sheet_Exception $e)
                {
                    $this->debug_row($e, $row_data, $sheet::DEBUG);
                }

                // останавливаем обработку, если возвращено false
                if ( $result === FALSE ) break;

                // $sheet->processed_rows_counter++;
            }
        }

        // освободим немного памяти
        $this->phpexcel->disconnectWorksheets();

        // соберём мусор
        gc_collect_cycles();
    }

    /**
     * возвращает значение ячейки в текущем листе
     * @param string $cell_column имя колонки в листе
     * @param string $cell_row имя столбца в листе
     * @param boolean $auto_format имя столбца в листе
     * @return mixed
     */
    public function get_cell_value($cell_column, $cell_row, $auto_format = false)
    {
        try
        {
            $pos = $cell_column . $cell_row;
            $obj_cell = $this->active_excel_sheet->getCell($pos);

            $value = $obj_cell->getValue();

            if ( $auto_format )
            {
                // для некорректных формул берём старое закешированное значение
                if ( substr($value,0,6) === '=#REF!' )
                {
                    $value = $obj_cell->getOldCalculatedValue();
                }
                else if(PHPExcel_Shared_Date::isDateTime($obj_cell))
                {
                    $value = date($this->date_format, PHPExcel_Shared_Date::ExcelToPHP($value));
                }
                else
                {
                    $value = $obj_cell->getFormattedValue();
                }
            }
        }
        catch(Exception $e) {
            $value = null;
        }

        return $value;
    }

    /**
     * возвращает сумму всех обработанных строк во всех листах
     * @return int
     */
    public function get_total_row_counter()
    {
        $counter = 0;

        // суммируем обработанные строки по всем листам
        foreach ( $this->sheet_list as $sheet )
        {
            $counter += $sheet->get_processed_rows_counter();
        }

        return $counter;
    }

    /**
     * возвращает массив имён листов
     * @return array
     */
    private function get_sheets_names()
    {
        $sl = array();

        foreach ( $this->sheet_list as $sheet )
        {
            $sl[] = $sheet->get_sheet_name();
        }

        return $sl;
    }

    /**
     * получить индекс листа в документе по его имени
     * @param string $name имя
     * @throws Import_Excel_Doc_Exception
     * @return int|null
     */
    private function get_sheet_index_by_name($name)
    {
        // получим список доступных листов
        $loadedSheetNames = $this->phpexcel->getSheetNames();

        // ищем индекс нужного листа
        $index = array_search($name, $loadedSheetNames);

        // если ничего не нашли, бросаем исключение
        if ( $index === false OR $index === null )
            throw new Import_Excel_Doc_Exception("Невозможно найти лист [$name] в присланном документе");

        return $index;
    }

    protected function debug_row($e, $row_data, $debug = TRUE)
    {
        if ( $debug )
        {
            echo '<pre>';
            print_r($row_data);
            echo '</pre>';
        }
        throw $e;
    }

    public function prepare_date($date, $output_format = NULL, $input_format = NULL)
    {
        $input_format   = $input_format     ?: $this->date_format;
        $output_format  = $output_format    ?: "Y-m-d H:i:s";

        // если форматы совпадают, ничего не надо делать
        if ( $input_format == $output_format )
        {
            return $date;
        }

        // преобразуем дату в объект
        $time = DateTime::createFromFormat( $input_format, $date);

        if ( !$time )
            throw new Import_Excel_Doc_Exception(
                "Неверный формат даты [$date], исправьте формат даты на ". $input_format
            );

        return $time->format($output_format);
    }

    public function finalize()
    {
        foreach ( $this->sheet_list as $sheet )
        {
            $sheet->finalize();
        }
    }
}
