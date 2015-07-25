<?php ( defined('SYSPATH') ) OR die('No direct script access.');


class CRM_Import_Abstract_Load_Doc extends CRM_Import_Abstract_Doc {

    /**
     * @param Import_Excel_Sheet|CRM_Import_Abstract_Sheet $sheet
     * @throws CRM_Import_Abstract_Load_Doc_Exception
     */
    public function use_sheet(Import_Excel_Sheet $sheet) // php interpreter trick
    {
        if ( ! $sheet instanceof CRM_Import_Abstract_Load_Sheet )
            throw new CRM_Import_Abstract_Load_Doc_Exception("Передаваемый объект листа должен быть экземпляром CRM_Import_Abstract_Load_Sheet");

        // сохраняем ссылку на объект-обработчик ошибок документа
        $sheet->set_e($this->errors);
        parent::use_sheet($sheet);
    }


    public function errors_factory()
    {
        return new CRM_Import_Abstract_Load_Errors;
    }
}

class CRM_Import_Abstract_Load_Doc_Exception extends CRM_Import_Abstract_Doc_Exception {}