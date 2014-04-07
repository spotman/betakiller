<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Assets_Provider {

    use Util_Factory;

    protected $_codename;

    /**
     * @var Assets_Storage
     */
    protected $_storage_instance;

    protected static function make_instance($class_name, $codename)
    {
        /** @var Assets_Provider $instance */
        $instance = new $class_name;
        $instance->set_codename($codename);
        return $instance;
    }

    public function set_codename($codename)
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
        // Security checks
        if ( ! Upload::not_empty($_file) OR ! Upload::valid($_file) )
            throw new Assets_Provider_Exception('Incorrect file, upload rejected');

        // Check permissions
        if ( ! $this->check_upload_permissions() )
            throw new Assets_Provider_Exception("Upload is not allowed");

        $full_path =    $_file['tmp_name'];

        // Put data into model
        $model = $this->file_model_factory()
            ->set_original_name($_file['name'])
            ->make_hash()
            ->set_size(filesize($full_path))
            ->set_mime($_file['type'])  // TODO Get type from file analysis, not from request
            ->set_uploaded_by($this->_get_current_user());

        // Custom processing
        $this->_upload($model);

        // Get file content
        $content = file_get_contents($full_path);

        // Place file into storage
        $this->get_storage()->put($model, $content);

        // Save model
        $model->save();

        return $model;
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
     * Returns User model
     *
     * @return Model_User|NULL
     */
    protected function _get_current_user()
    {
        return Auth::instance()->get_user();
    }

    /**
     * @return Assets_Storage
     */
    protected function get_storage()
    {
        if ( ! $this->_storage_instance )
        {
            $this->_storage_instance = $this->storage_factory();
        }

        return $this->_storage_instance;
    }

    /**
     * Returns concrete storage for current provider
     *
     * @return Assets_Storage
     */
    abstract protected function storage_factory();

    /**
     * Creates empty file model
     *
     * @return Assets_File_Model
     */
    abstract protected function file_model_factory();

    /**
     * Returns TRUE if upload is granted
     *
     * @return bool
     */
    abstract protected function check_upload_permissions();

    /**
     * Additional upload processing
     *
     * @param Assets_File_Model $model
     */
    abstract protected function _upload(Assets_File_Model $model);

    /**
     * Returns TRUE if delete operation granted
     *
     * @param Assets_File_Model $model
     * @return bool
     */
    abstract protected function check_delete_permissions(Assets_File_Model $model);

}