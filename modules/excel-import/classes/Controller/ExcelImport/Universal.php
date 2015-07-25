<?php

abstract class Controller_ExcelImport_Universal extends Controller_ExcelImport_Abstract {

    /** @var array перечень листов, которые необходимо обработать (попроектно) */
    protected $sheet_config = array();

    protected $_action_name = NULL;

    public function prepare_doc()
    {
        $project_id = Env::get('project', 'crm')->id;

        if( !isset( $this->sheet_config[ $project_id ] ) )
            throw new ErrorException("Импорт для данного проекта не настроен");

        $sheet_list = $this->sheet_config[ $project_id ];

        foreach($sheet_list AS $sheet_name)
        {
            $this->doc->use_sheet( $this->sheet_factory($sheet_name) );

        }
    }

    protected function sheet_factory($sheet_name)
    {
        $sheet_class = 'CRM_Import_'. $sheet_name;
        return new $sheet_class;
    }

    protected function error_factory()
    {
        $this->doc->errors_factory($this->_action_name);
    }

    protected function get_active_projects()
    {
        $active = parent::get_active_projects();

        $projects = array();

        foreach($active as $id => $data)
        {
            if( array_key_exists($id, $this->sheet_config) ) $projects[ $id ] = $data;
        }

        return $projects;
    }

}