<?php defined('SYSPATH') OR die('No direct script access.');


abstract class CRM_Import_Abstract_Doc extends Import_Excel_Doc {

    /** @var CRM_Import_Abstract_Errors */
    public $errors;


    public function __construct($filename)
    {
        parent::__construct($filename);
        $this->errors = $this->errors_factory();
    }

    abstract public function errors_factory();

    public function get_formatted_errors()
    {
        return $this->errors->get_formatted_errors();
    }

    public function log_error($number, $error_type, $msg = null)
    {
        $this->errors->log_error($number, $error_type, $msg);
    }

    public function log_exception(Exception $e)
    {
        $errors = $this->errors;

        $this->log_error('', $errors::ERROR_DOC_EXCEPTION, $e->getMessage());
    }

    public function process()
    {
        try
        {
            parent::process();
        }
        catch(Import_Excel_Doc_Exception $e)
        {
            $this->log_exception($e);
        }
        catch(Import_Excel_Sheet_Exception $e)
        {
            $this->log_exception($e);
        }

    }

    public function finalize()
    {
        parent::finalize();
    }

}

abstract class CRM_Import_Abstract_Doc_Exception extends Import_Excel_Doc_Exception {}
