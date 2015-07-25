<?php

class Controller_ExcelImport_Actualize_Orders extends Controller_ExcelImport_Universal {

    protected $sheet_config = array(
        // 1 => array('ExcelSheet'),
    );

    public function doc_factory($filename)
    {
        return new CRM_Import_Abstract_Actualize_Doc($filename);
    }

    /** хелпер для создания url в пределах текущего контроллера */
    protected function url($action = 'index')
    {
        return '/'.Route::get('excel-import-actualize')->uri(array(
            'controller'    => Request::initial()->controller(),
            'action'        => $action
        ));
    }
}
