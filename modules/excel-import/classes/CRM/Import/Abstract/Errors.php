<?php


abstract class CRM_Import_Abstract_Errors {

    /** ошибка "Ошибка разбора документа" */
    const ERROR_DOC_EXCEPTION = 0;
    /**
     * реестр ошибок
     * @var array
     */
    protected $e = array();

    /**
     * @param $number номер телефона из заявки
     * @param $error_type тип ошибки (константа ERROR_...)
     * @param string $msg опциональное сообщение об ошибке, которое будет дописано к основному
     */
    abstract public function log_error($number, $error_type, $msg = null);

    /**
     * возвращает отформатированные ошибки
     * @return array
     */
    public function get_formatted_errors()
    {
        return $this->e;
    }
}