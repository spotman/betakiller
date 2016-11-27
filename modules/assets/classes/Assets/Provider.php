<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Config\ConfigInterface;

abstract class Assets_Provider {

    const CONFIG_KEY = 'assets';

    /**
     * @var string
     */
    protected $_codename;

    /**
     * @var Assets_Storage
     */
    protected $_storage_instance;

    /**
     * @Inject
     * @var ConfigInterface
     */
    protected $_config;

    /**
     * @var \Model_User
     */
    protected $_user;


    public function __construct(ConfigInterface $config, \Model_User $user)
    {
        $this->_config = $config;
        $this->_user = $user;
    }

    public function get_assets_config_value(array $path)
    {
        return $this->_config->load(array_merge([self::CONFIG_KEY], $path));
    }

    public function get_assets_provider_config_value(array $path, $codename = null)
    {
        $codename = $codename ?: $this->_codename;

        return $this->get_assets_config_value(array_merge(['providers', $codename], $path));
    }

//    protected function get_permissions_config_value($type)
//    {
//        return $this->get_assets_provider_config_value('permissions.'.$type);
//    }

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
     * @param Assets_ModelInterface $model
     * @return string
     */
    public function get_original_url(Assets_ModelInterface $model)
    {
        return $this->_get_item_url('original', $model);
    }

    /**
     * Returns URL for deleting provided file
     *
     * @param Assets_ModelInterface $model
     * @return string
     */
    public function get_delete_url(Assets_ModelInterface $model)
    {
        return $this->_get_item_url('delete', $model);
    }

    protected function _get_item_url($action, Assets_ModelInterface $model)
    {
        $url = $model->get_url();

        if ( ! $url )
            throw new Assets_Provider_Exception('Model must have url');

        $options = array(
            'provider'  =>  $this->_codename,
            'action'    =>  $action,
            'item_url'  =>  $url,
            'ext'       =>  $this->get_model_extension($model),
        );

        return Route::url('assets-provider-item', $options);
    }

    public function get_model_extension(Assets_ModelInterface $model)
    {
        $mime = $model->get_mime();
        $extensions = File::exts_by_mime($mime);

        if ( !$extensions )
            throw new Assets_Exception('MIME :mime has no defined extension', array(':mime' => $mime));

        return array_pop($extensions);
    }

    /**
     * @param array $_file Item from $_FILES
     * @param array $_post_data Array with items from $_POST
     * @return Assets_ModelInterface
     * @throws Assets_Provider_Exception
     */
    public function upload(array $_file, array $_post_data)
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

        $full_path = $_file['tmp_name'];
        $safe_name = strip_tags($_file['name']);

        return $this->store($full_path, $safe_name, $_post_data);
    }

    public function store($full_path, $original_name, array $_post_data = array())
    {
        // Check permissions
        if ( ! $this->check_store_permissions() )
            throw new Assets_Provider_Exception('Store is not allowed');

        // Get type from file analysis
        $mime_type = $this->get_mime_type($full_path);

        // MIME-type check
        $this->check_allowed_mime_types($mime_type);

        // Init model
        $model = $this->file_model_factory();

        // Get file content
        $content = file_get_contents($full_path);

        // Custom processing
        $content = $this->_upload($model, $content, $_post_data, $full_path);

        // Put data into model
        $model
            ->set_original_name($original_name)
            ->set_size(strlen($content))
            ->set_mime($mime_type)
            ->set_uploaded_by($this->_user);

        // Place file into storage
        $this->get_storage()->put($model, $content);

        // Save model
        $model->save();

        $this->_post_upload($model, $_post_data);

        return $model;
    }

    protected function get_mime_type($file_path)
    {
        return File::mime($file_path);
    }

    /**
     * Custom upload processing
     *
     * @param Assets_ModelInterface $model
     * @param string $content
     * @param array $_post_data
     * @param string $file_path Full path to source file
     * @return string
     */
    protected function _upload($model, $content, array $_post_data, $file_path)
    {
        // Dummy operation for CS check
        unset($model, $_post_data, $file_path);

        // Empty by default
        return $content;
    }

    /**
     * After upload processing
     *
     * @param Assets_ModelInterface $model
     * @param array $_post_data
     */
    protected function _post_upload($model, array $_post_data)
    {
        // Empty by default
    }

    public function deploy(Request $request, Assets_ModelInterface $model, $content)
    {
        $deploy_allowed = (bool) $this->get_assets_config_value(['deploy', 'enabled']);

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
            $mask = $this->get_assets_config_value(['deploy', 'directory_mask']);
            mkdir($path, $mask, true);
        }

        $filename = $this->_get_item_deploy_filename($request);

        // Make deploy filename
        $full_path = $path.DIRECTORY_SEPARATOR.$filename;

        file_put_contents($full_path, $content);
    }

    protected function _get_item_deploy_filename(Request $request)
    {
        return $request->action().'.'.$request->param('ext');
    }

    /**
     * @param Assets_ModelInterface $model
     * @throws Assets_Provider_Exception
     */
    public function delete(Assets_ModelInterface $model)
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
    }

    /**
     * Additional delete processing
     *
     * @param Assets_ModelInterface $model
     */
    protected function _delete($model)
    {
        // Empty by default
    }

    /**
     * Returns asset file model with provided hash
     *
     * @param $url
     * @return Assets_ModelInterface|NULL
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
     * @param Assets_ModelInterface $model
     * @return string
     */
    public function get_content(Assets_ModelInterface $model)
    {
        // Get file from storage
        return $this->get_storage()->get($model);
    }

    /**
     * Update content of the file
     *
     * @param Assets_ModelInterface $model
     * @param string $content
     */
    public function set_content(Assets_ModelInterface $model, $content)
    {
        $this->get_storage()->put($model, $content);

        // Drop deployed cache for current asset
        $this->_drop_deploy_cache($model);
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
     * @return bool
     */
    public function check_allowed_mime_types($mime)
    {
        $allowed_mimes = $this->get_allowed_mime_types();

        // All MIMEs are allowed
        if ( $allowed_mimes === TRUE )
            return TRUE;

        if ( ! is_array($allowed_mimes) )
            throw new Assets_Provider_Exception('Allowed MIME-types in :codename provider must be an array() or TRUE',
                array(':codename' => $this->_codename)
            );

        // Check allowed MIMEs
        foreach ( $allowed_mimes as $allowed )
        {
            if ( $mime == $allowed )
                return TRUE;
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
     * @param Assets_ModelInterface $model
     * @return string
     * @throws Assets_Provider_Exception
     */
    protected function _get_item_deploy_path(Assets_ModelInterface $model)
    {
        $model_url = $model->get_url();

        if ( ! $model_url )
            throw new Assets_Provider_Exception('Model must have url');

        $options = array(
            'provider'  =>  $this->_codename,
            'item_url'  =>  $model_url,
        );

        $url = Route::url('assets-provider-item-deploy-directory', $options);

        $path = parse_url($url, PHP_URL_PATH);

        return $this->get_doc_root().DIRECTORY_SEPARATOR.ltrim($path, '/');
    }

    protected function get_doc_root()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * Removes all deployed versions of provided asset
     *
     * @param Assets_ModelInterface $model
     */
    protected function _drop_deploy_cache(Assets_ModelInterface $model)
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
    abstract public function get_allowed_mime_types();

    /**
     * Returns concrete storage for current provider
     *
     * @return Assets_Storage
     */
    abstract protected function storage_factory();

    /**
     * Creates empty file model
     *
     * @return Assets_ModelInterface
     */
    abstract public function file_model_factory();

    /**
     * Returns TRUE if upload is granted
     *
     * @return bool
     */
    abstract protected function check_upload_permissions();

    /**
     * Returns TRUE if deploy is granted
     *
     * @param Assets_ModelInterface $model
     * @return bool
     */
    abstract protected function check_deploy_permissions($model);

    /**
     * Returns TRUE if delete operation granted
     *
     * @param Assets_ModelInterface $model
     * @return bool
     */
    abstract protected function check_delete_permissions($model);

    /**
     * Returns TRUE if store is granted
     *
     * @return bool
     */
    protected function check_store_permissions()
    {
        return $this->check_upload_permissions();
    }
}
