<?php defined('SYSPATH') OR die('No direct script access.');


abstract class Import_Excel_Sheet {

    /**
     * при включении режима редактирования в дочернем листе,
     * в стандартный поток будет выводиться дополнительная инфа об ошибках
     */
    const DEBUG = FALSE;

//    /** @var string название листа в документе */
//    public $sheet_name = null;

//    /** @var int кол-во строк, составляющих шапку документа (включая строку с названиями столбцов) */
//    public $header_size = null;

//    /** @var int номер строки (в терминах Excel), содержащей заголовки столбцов */
//    public $column_title_row = null;

//    /** @var array структура столбцов в документе: <буква или внутренняя константа> => <заголовок столбца> */
//    public $column_structure = array();

//    /** @var array массив букв столбцов, в которых нужно произвести автоформат значений; по-умолчанию отключено для всех ячеек */
//    public $auto_format_cells = array();

    /** @var int количество обработанных строк */
    protected $processed_rows_counter = 0;

    /** @var Import_Excel_Doc объект, описывающий документ, частью которого является текущий лист */
    protected $doc;

    /**
     * Название листа в документе
     * @return string
     */
    abstract public function get_sheet_name();

    /**
     * Кол-во строк, составляющих шапку документа (включая строку с названиями столбцов)
     * @return int
     */
    abstract public function get_header_size();

    /**
     * Номер строки (в терминах Excel), содержащей заголовки столбцов
     * @return int
     */
    abstract public function get_column_title_row();

    /**
     * Структура столбцов в документе: <буква или внутренняя константа> => <заголовок столбца>
     * @return array
     */
    abstract public function get_column_structure();

    /**
     * Массив букв столбцов, в которых нужно произвести автоформат значений;
     * по-умолчанию отключено для всех ячеек
     * @return array
     */
    public function get_auto_format_cells()
    {
        return array();
    }

    /**
     * обработчик строки в листе документа
     * если нужно остановить обработку документа, можно вернуть false или выбросить исключение
     * @abstract
     * @param array $row_data
     */
    abstract public function process_row(array $row_data);

    /**
     * необязательный "hook", вызываемый после добавления листа в очередь на обработку
     * в Import_Excel_Doc->use_sheet() ]
     */
    public function init() {}

    /**
     * @return int
     */
    public function get_processed_rows_counter()
    {
        return $this->processed_rows_counter;
    }

    /**
     * сохраняет объект документа внутри объекта листа (для хелперов)
     * @param Import_Excel_Doc $doc документ, к которому привязан лист
     */
    public function set_doc(Import_Excel_Doc $doc)
    {
        $this->doc = $doc;
    }

    /**
     * хелпер, возвращает значение ячейки в текущем листе
     * @param string $cell_column имя колонки в листе
     * @param string $cell_row имя столбца в листе
     * @return mixed
     */
    public function get_cell_value($cell_column, $cell_row)
    {
        return $this->doc->get_cell_value($cell_column, $cell_row);
    }

    protected function increment_processed_row_counter()
    {
        $this->processed_rows_counter++;
    }

    /**
     * @param $date string
     * @param string $output_format
     * @param string $input_format
     * @return string
     * @throws Import_Excel_Sheet_Exception
     */
    protected function prepare_date($date, $output_format = null , $input_format = null)
    {
        try
        {
            return $this->doc->prepare_date($date, $output_format, $input_format);
        }
        catch( Import_Excel_Doc_Exception $e)
        {
            throw new Import_Excel_Sheet_Exception($e->getMessage());
        }
    }

    /**
     * метод-хук, вызываемый после обработки листа
     * может быть переопределён в дочерних классах
     */
    public function finalize() {}

}
