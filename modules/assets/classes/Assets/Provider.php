<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Assets_Provider {

    use Util_Factory;

    /**
     * @var string
     */
    protected $_codename;

    /**
     * @var Assets_Storage
     */
    protected $_storage_instance;

    /**
     * @var Array
     */
    protected static $_config;

    public static function config($key, $default_value = NULL)
    {
        if ( static::$_config === NULL )
        {
            static::$_config = Kohana::config('assets')->as_array();
        }

        return Arr::path(static::$_config, $key, $default_value);
    }

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
        $options = array(
            'provider'  =>  $this->_codename,
        );

        return Route::url('assets-provider-upload', $options);
    }

    /**
     * Returns public URL for provided model
     *
     * @param Assets_Model $model
     * @return string
     */
    public function get_original_url(Assets_Model $model)
    {
        return $this->_get_item_url('original', $model);
    }

    /**
     * Returns URL for deleting provided file
     *
     * @param Assets_Model $model
     * @return string
     */
    public function get_delete_url(Assets_Model $model)
    {
        return $this->_get_item_url('delete', $model);
    }

    protected function _get_item_url($action, Assets_Model $model)
    {
        $url = $model->get_url();

        if ( ! $url )
            throw new Assets_Provider_Exception('Model must have url');

        $options = array(
            'provider'  =>  $this->_codename,
            'action'    =>  $action,
            'item_url'  =>  $url,
        );

        return Route::url('assets-provider-item', $options);
    }

    /**
     * @param array $_file Item from $_FILES
     * @return Assets_Model
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

        // Init model
        $model = $this->file_model_factory();

        // Get file content
        $content = file_get_contents($full_path);

        // Custom processing
        $content = $this->_upload($model, $content);

        // Put data into model
        $model
            ->set_original_name($safe_name)
            ->set_size(strlen($content))
            ->set_mime($mime_type)
            ->set_uploaded_by($this->_get_current_user());


        // Place file into storage
        $this->get_storage()->put($model, $content);

        // Save model
        $model->save();

        return $model;
    }

    /**
     * Additional upload processing
     *
     * @param Assets_Model $model
     * @param string $content
     * @return string
     */
    protected function _upload($model, $content)
    {
        // Empty by default
        return $content;
    }

    public function deploy(Request $request, Assets_Model $model, $content)
    {
        // TODO Move to config
        $deploy_allowed = Kohana::in_production();

        // No deployment in testing and developing environments
        if ( ! $deploy_allowed )
            return;

        // Check permissions
        if ( ! $this->check_deploy_permissions($model) )
            return;

        // Get item base deploy path
        $path = $this->_get_item_deploy_path($model);

        // Create deploy path if not exists
        if ( ! file_exists($path) )
        {
            $mask = 0775; // TODO Move to config
            mkdir($path, $mask, true);
        }

        $filename = $this->_get_item_deploy_filename($request);

        // Make deploy filename
        $full_path = $path.DIRECTORY_SEPARATOR.$filename;

        file_put_contents($full_path, $content);
    }

    protected function _get_item_deploy_filename(Request $request)
    {
        return $request->action();
    }

    /**
     * @param Assets_Model $model
     * @throws Assets_Provider_Exception
     */
    public function delete(Assets_Model $model)
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
     * @param Assets_Model $model
     */
    protected function _delete($model)
    {
        // Empty by default
    }

    /**
     * Returns asset file model with provided hash
     *
     * @param $url
     * @return Assets_Model|NULL
     * @throws Assets_Provider_Exception
     */
    public function get_model_by_deploy_url($url)
    {
        // Find model by hash
        $model = $this->file_model_factory()->by_url($url);

        if ( ! $model )
            throw new Assets_Provider_Exception('Can not find file with url = :url', array(':url' => $url));

        return $model;
    }

    /**
     * Returns content of the file
     *
     * @param Assets_Model $model
     * @return string
     */
    public function get_content(Assets_Model $model)
    {
        // Get file from storage
        return $this->get_storage()->get($model);
    }

    /**
     * Update content of the file
     *
     * @param Assets_Model $model
     * @param string $content
     */
    public function set_content(Assets_Model $model, $content)
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

    /**
     * Returns asset`s base deploy directory
     *
     * @param Assets_Model $model
     * @return string
     * @throws Assets_Provider_Exception
     */
    protected function _get_item_deploy_path(Assets_Model $model)
    {
        $model_url = $model->get_url();

        if ( ! $model_url )
            throw new Assets_Provider_Exception('Model must have url');

        $options = array(
            'provider'  =>  $this->_codename,
            'item_url'  =>  $model_url,
        );

        $url = Route::url('assets-provider-item-deploy-directory', $options);

        // TODO Security
        $doc_root = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;

        return $doc_root . ltrim($url, '/');
    }

    /**
     * Removes all deployed versions of provided asset
     *
     * @param Assets_Model $model
     */
    protected function _drop_deploy_cache(Assets_Model $model)
    {
        $path = $this->_get_item_deploy_path($model);

        if ( ! file_exists($path) )
            return;

        // Remove all versions of file
        foreach ( glob("{$path}/*") as $file )
        {
            unlink($file);
        }

        // Remove directory itself
        rmdir($path);
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
     * @return Assets_Model
     */
    abstract protected function file_model_factory();

    /**
     * Returns TRUE if upload is granted
     *
     * @return bool
     */
    abstract protected function check_upload_permissions();

    /**
     * Returns TRUE if deploy is granted
     *
     * @param Assets_Model $model
     * @return bool
     */
    abstract protected function check_deploy_permissions($model);

    /**
     * Returns TRUE if delete operation granted
     *
     * @param Assets_Model $model
     * @return bool
     */
    abstract protected function check_delete_permissions($model);

}