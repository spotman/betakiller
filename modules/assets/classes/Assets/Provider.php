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
        return Route::url('assets-provider-upload');
    }

    /**
     * Returns public URL for provided model
     *
     * @param Assets_File_Model $model
     * @return string
     */
    public function get_public_url(Assets_File_Model $model)
    {
        return $this->_get_item_url('public', $model);
    }

    /**
     * Returns URL for deleting provided file
     *
     * @param Assets_File_Model $model
     * @return string
     */
    public function get_delete_url(Assets_File_Model $model)
    {
        return $this->_get_item_url('delete', $model);
    }

    protected function _get_item_url($action, Assets_File_Model $model)
    {
        $hash = $model->get_hash();

        if ( ! $hash )
            throw new Assets_Provider_Exception('Model must have hash');

        $options = array(
            'provider'  =>  $this->_codename,
            'action'    =>  $action,
            'hash'      =>  $hash,
        );

        return Route::url('assets-provider-item', $options);
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

        // Security checks
        if ( ! Upload::not_empty($_file) OR ! Upload::valid($_file) )
        {
            // TODO Разные сообщения об ошибках в тексте исключения (файл слишком большой, итд)
            throw new Assets_Exception_Upload('Incorrect file, upload rejected');
        }

        // TODO Get type from file analysis, not from request
        $mime_type = HTML::chars( strip_tags($_file['type']) );

        // MIME-type check
        $this->check_allowed_mime_types($mime_type);

        $full_path = $_file['tmp_name'];
        $safe_name = strip_tags($_file['name']);

        // Put data into model
        $model = $this->file_model_factory()
            ->set_original_name($safe_name)
            ->make_hash()
            ->set_size(filesize($full_path))
            ->set_mime($mime_type)
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
     * Additional upload processing
     *
     * @param Assets_File_Model $model
     */
    protected function _upload($model)
    {
        // Empty by default
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

        // Custom delete processing
        $this->_delete($model);

        // Remove file from storage
        $this->get_storage()->delete($model);

        // Drop deployed cache for current asset
        $this->_drop_deploy_cache($model);

        // Remove model
        $model->delete();
    }

    /**
     * Additional delete processing
     *
     * @param Assets_File_Model $model
     */
    protected function _delete($model)
    {
        // Empty by default
    }

    /**
     * Returns asset file model with provided hash
     *
     * @param $hash
     * @return Assets_File_Model|NULL
     * @throws Assets_Provider_Exception
     */
    public function get_model_by_hash($hash)
    {
        // Find model by hash
        $model = $this->file_model_factory()->by_hash($hash);

        if ( ! $model )
            throw new Assets_Provider_Exception('Can not find file with hash = :hash', array(':hash' => $hash));

        return $model;
    }

    /**
     * Returns content of the file
     *
     * @param Assets_File_Model $model
     * @return string
     */
    public function get_content(Assets_File_Model $model)
    {
        // Get file from storage
        return $this->get_storage()->get($model);
    }

    /**
     * Update content of the file
     *
     * @param Assets_File_Model $model
     * @param string $content
     */
    public function set_content(Assets_File_Model $model, $content)
    {
        $this->get_storage()->put($model, $content);

        // Drop deployed cache for current asset
        $this->_drop_deploy_cache($model);
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
     * Returns TRUE if MIME-type is allowed in current provider
     *
     * @param string $mime MIME-type
     * @throws Assets_Provider_Exception
     */
    protected function check_allowed_mime_types($mime)
    {
        $allowed_mimes = $this->get_allowed_mime_types();

        // All MIMEs are allowed
        if ( $allowed_mimes === TRUE )
            return;

        if ( ! is_array($allowed_mimes) )
            throw new Assets_Provider_Exception('Allowed MIME-types in :codename provider must be an array() or TRUE',
                array(':codename' => $this->_codename)
            );

        // Check allowed MIMEs
        foreach ( $allowed_mimes as $allowed )
        {
            if ( $mime == $allowed )
                return;
        }

        $allowed_extensions = array();

        foreach ( $allowed_mimes as $allowed_mime )
        {
            $allowed_extensions = array_merge($allowed_extensions, File::exts_by_mime($allowed_mime));
        }

        throw new Assets_Exception_Upload('You may upload files with :ext extensions only',
            array(':ext' => implode(', ', $allowed_extensions))
        );
    }

    protected function _drop_deploy_cache(Assets_File_Model $model)
    {
        // TODO Implement
    }

    /**
     * Returns list of allowed MIME-types (or TRUE if all MIMEs are allowed)
     *
     * @return array|TRUE
     */
    abstract protected function get_allowed_mime_types();

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
     * Returns TRUE if delete operation granted
     *
     * @param Assets_File_Model $model
     * @return bool
     */
    abstract protected function check_delete_permissions($model);

}