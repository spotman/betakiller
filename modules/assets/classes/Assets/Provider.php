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
     * @param array $_file Item from $_FILES
     * @return Assets_File_Model
     * @throws Assets_Provider_Exception
     */
    public function upload(array $_file)
    {
        // Check permissions
        if ( ! $this->check_upload_permissions() )
            throw new Assets_Provider_Exception("Upload is not allowed");

        // TODO Get file content
        $content = '';


        $model = $this->file_model_factory();

        // TODO Put data into model

        // Place file into storage
        $this->get_storage()->put($model, $content);

        // Save model
        $model->save();
    }

    /**
     * @param Assets_File_Model $model
     * @throws Assets_Provider_Exception
     */
    public function delete(Assets_File_Model $model)
    {
        // Check permissions
        if ( ! $this->check_delete_permissions($model) )
            throw new Assets_Provider_Exception("Delete is not allowed");

        // Remove file from storage
        $this->get_storage()->delete($model);

        // Remove model
        $model->delete();
    }

    /**
     * @return Assets_Storage
     */
    abstract protected function get_storage();

    /**
     * Creates empty file model
     *
     * @return Assets_File_Model
     */
    abstract protected function file_model_factory();

    /**
     * TODO
     * @return bool
     */
    abstract protected function check_upload_permissions();

    /**
     * @param Assets_File_Model $model
     * @return bool
     */
    abstract protected function check_delete_permissions(Assets_File_Model $model);

}