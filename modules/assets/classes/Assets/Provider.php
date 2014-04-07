<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Assets_Provider {

    use Util_Factory;

    protected $_codename;

    protected static function make_instance($class_name, $codename)
    {
        return new $class_name($codename);
    }

    public function __construct($codename)
    {
        $this->_codename = $codename;
    }

    /**
     * Returns URL for POSTing new files
     *
     * @return string
     */
    public function get_upload_url()
    {
        return Route::url('assets-provider-action', array(
            'provider'  =>  $this->_codename,
            'action'    =>  'upload'
        ));
    }

    /**
     * @param Assets_File_Model $model
     * @return bool
     * @throws Assets_Provider_Exception
     */
    public function delete(Assets_File_Model $model)
    {
        // Check permissions
        if ( ! $this->check_delete_permissions($model) )
            throw new Assets_Provider_Exception("Delete is not allowed");

        // TODO Remove file from storage

        // TODO Remove model
        $model->delete();

        return TRUE;
    }

    // TODO
    abstract protected function get_storage(Assets_File_Model $model);

    /**
     * @param Assets_File_Model $model
     * @return bool
     */
    abstract protected function check_delete_permissions(Assets_File_Model $model);

}