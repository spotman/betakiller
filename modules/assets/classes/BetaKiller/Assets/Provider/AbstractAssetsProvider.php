<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\AssetsException;
use BetaKiller\Assets\AssetsExceptionUpload;
use BetaKiller\Assets\AssetsProviderException;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Storage\AbstractAssetsStorage;
use BetaKiller\Config\ConfigInterface;
use BetaKiller\Model\UserInterface;
use DateTime;
use File;
use Request;
use Route;
use Upload;

abstract class AbstractAssetsProvider
{
    const CONFIG_KEY = 'assets';
    const CONFIG_PROVIDERS_KEY = 'providers';
    const CONFIG_URL_KEY = 'url_key';

    /**
     * @var string
     */
    protected $codename;

    /**
     * @var AbstractAssetsStorage
     */
    private $storageInstance;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UserInterface
     */
    private $user;

    public function __construct(ConfigInterface $config, UserInterface $user)
    {
        $this->config = $config;
        $this->user   = $user;
    }

    public function getAssetsConfigValue(array $path)
    {
        return $this->config->load(array_merge([self::CONFIG_KEY], $path));
    }

    /**
     * @param array       $path
     * @param string|null $codename
     *
     * @return array|\BetaKiller\Config\ConfigGroupInterface|null|string
     */
    protected function getAssetsProviderConfigValue(array $path, $codename = null)
    {
        $codename = $codename ?: $this->codename;

        return $this->getAssetsConfigValue(array_merge([self::CONFIG_PROVIDERS_KEY, $codename], $path));
    }

    public function setCodename($codename)
    {
        $this->codename = $codename;
    }

    /**
     * Returns URL for POSTing new files
     *
     * @return string
     */
    public function getUploadUrl()
    {
        $options = [
            'provider' => $this->getUrlKey(),
        ];

        // TODO Remove Route dependency
        return Route::url('assets-provider-upload', $options);
    }

    public function getUrlKey()
    {
        return $this->getUrlKeyConfigValue() ?: $this->codename;
    }

    private function getUrlKeyConfigValue()
    {
        return $this->getAssetsProviderConfigValue([self::CONFIG_URL_KEY]);
    }

    /**
     * Returns public URL for provided model
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function getOriginalUrl(AssetsModelInterface $model)
    {
        return $this->getItemUrl('original', $model);
    }

    /**
     * Returns URL for deleting provided file
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function getDeleteUrl(AssetsModelInterface $model)
    {
        return $this->getItemUrl('delete', $model);
    }

    protected function getItemUrl($action, AssetsModelInterface $model)
    {
        $url = $model->getUrl();

        if (!$url) {
            throw new AssetsProviderException('Model must have url');
        }

        $options = [
            'provider' => $this->getUrlKey(),
            'action'   => $action,
            'item_url' => $url,
            'ext'      => $this->getModelExtension($model),
        ];

        return Route::url('assets-provider-item', $options);
    }

    public function getModelExtension(AssetsModelInterface $model)
    {
        $mime       = $model->getMime();
        $extensions = File::exts_by_mime($mime);

        if (!$extensions) {
            throw new AssetsException('MIME :mime has no defined extension', [':mime' => $mime]);
        }

        return array_pop($extensions);
    }

    /**
     * @param array $_file      Item from $_FILES
     * @param array $_post_data Array with items from $_POST
     *
     * @return AssetsModelInterface
     * @throws AssetsProviderException
     */
    public function upload(array $_file, array $_post_data)
    {
        // Check permissions
        if (!$this->checkUploadPermissions()) {
            throw new AssetsProviderException('Upload is not allowed');
        }

        // Security checks
        if (!Upload::not_empty($_file) || !Upload::valid($_file)) {
            // TODO Разные сообщения об ошибках в тексте исключения (файл слишком большой, итд)
            throw new AssetsExceptionUpload('Incorrect file, upload rejected');
        }

        $full_path = $_file['tmp_name'];
        $safe_name = strip_tags($_file['name']);

        return $this->store($full_path, $safe_name, $_post_data);
    }

    public function store($fullPath, $originalName, array $postData = [])
    {
        // Check permissions
        if (!$this->checkStorePermissions()) {
            throw new AssetsProviderException('Store is not allowed');
        }

        // Get type from file analysis
        $mime_type = $this->getFileMimeType($fullPath);

        // MIME-type check
        $this->checkAllowedMimeTypes($mime_type);

        // Init model
        $model = $this->createFileModel();

        // Get file content
        $content = file_get_contents($fullPath);

        // Custom processing
        $content = $this->customUploadProcessing($model, $content, $postData, $fullPath);

        // Put data into model
        $model
            ->setOriginalName($originalName)
            ->setSize(strlen($content))
            ->setMime($mime_type)
            ->setUploadedBy($this->user);

        // Place file into storage
        $this->getStorage()->put($model, $content);

        // Save model
        $model->save();

        $this->postUploadProcessing($model, $postData);

        return $model;
    }

    protected function getFileMimeType($file_path)
    {
        return File::mime($file_path);
    }

    /**
     * Custom upload processing
     *
     * @param AssetsModelInterface $model
     * @param string               $content
     * @param array                $postData
     * @param string               $filePath Full path to source file
     *
     * @return string
     */
    protected function customUploadProcessing($model, $content, array $postData, $filePath)
    {
        // Empty by default
        return $content;
    }

    /**
     * After upload processing
     *
     * @param AssetsModelInterface $model
     * @param array                $_post_data
     */
    protected function postUploadProcessing($model, array $_post_data)
    {
        // Empty by default
    }

    public function deploy(Request $request, AssetsModelInterface $model, $content)
    {
        $deployAllowed = (bool)$this->getAssetsConfigValue(['deploy', 'enabled']);

        // No deployment in testing and developing environments
        if (!$deployAllowed) {
            return;
        }

        // Check permissions
        if (!$this->checkDeployPermissions($model)) {
            return;
        }

        // Get item base deploy path
        $path = $this->getItemDeployPath($model);

        // Create deploy path if not exists
        if (!file_exists($path)) {
            $mask = $this->getAssetsConfigValue(['deploy', 'directory_mask']);
            if (!@mkdir($path, $mask, true) && !is_dir($path)) {
                throw new AssetsProviderException('Can not create path :value', [
                    ':value' => $path,
                ]);
            }
        }

        $filename = $this->getItemDeployFilename($request);

        // Make deploy filename
        $fulPath = $path.DIRECTORY_SEPARATOR.$filename;

        file_put_contents($fulPath, $content);

        // Update last modification time for better caching
        $lastModified = $model->getLastModifiedAt() ?: new DateTime();
        touch($fulPath, $lastModified->getTimestamp());
    }

    protected function getItemDeployFilename(Request $request)
    {
        return $request->action().'.'.$request->param('ext');
    }

    /**
     * @param AssetsModelInterface $model
     *
     * @throws AssetsProviderException
     */
    public function delete(AssetsModelInterface $model)
    {
        // Check permissions
        if (!$this->checkDeletePermissions($model)) {
            throw new AssetsProviderException('Delete is not allowed');
        }

        // Custom delete processing
        $this->_delete($model);

        // Remove file from storage
        $this->getStorage()->delete($model);

        // Drop deployed cache for current asset
        $this->dropDeployCache($model);
    }

    /**
     * Additional delete processing
     *
     * @param AssetsModelInterface $model
     */
    protected function _delete($model)
    {
        // Empty by default
    }

    /**
     * Returns asset file model with provided hash
     *
     * @param $url
     *
     * @return AssetsModelInterface|NULL
     * @throws AssetsProviderException
     */
    public function getModelByDeployUrl($url)
    {
        // Find model by hash
        $model = $this->createFileModel()->byUrl($url);

        if (!$model) {
            throw new AssetsProviderException('Can not find file with url = :url', [':url' => $url]);
        }

        return $model;
    }

    /**
     * Returns content of the file
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function getContent(AssetsModelInterface $model)
    {
        // Get file from storage
        return $this->getStorage()->get($model);
    }

    /**
     * Update content of the file
     *
     * @param AssetsModelInterface $model
     * @param string               $content
     */
    public function setContent(AssetsModelInterface $model, $content)
    {
        $this->getStorage()->put($model, $content);

        // Drop deployed cache for current asset
        $this->dropDeployCache($model);
    }

    /**
     * @return AbstractAssetsStorage
     */
    protected function getStorage()
    {
        if (!$this->storageInstance) {
            $this->storageInstance = $this->createStorage();
        }

        return $this->storageInstance;
    }

    /**
     * Returns TRUE if MIME-type is allowed in current provider
     *
     * @param string $mime MIME-type
     *
     * @throws AssetsProviderException
     * @return bool
     */
    public function checkAllowedMimeTypes($mime)
    {
        $allowedMimeTypes = $this->getAllowedMimeTypes();

        // All MIMEs are allowed
        if ($allowedMimeTypes === true) {
            return true;
        }

        if (!is_array($allowedMimeTypes)) {
            throw new AssetsProviderException('Allowed MIME-types in :codename provider must be an array() or TRUE',
                [':codename' => $this->codename]
            );
        }

        // Check allowed MIMEs
        foreach ($allowedMimeTypes as $allowed) {
            if ($mime === $allowed) {
                return true;
            }
        }

        $allowedExtensions = [];

        foreach ($allowedMimeTypes as $allowedMime) {
            $allowedExtensions = array_merge($allowedExtensions, File::exts_by_mime($allowedMime));
        }

        throw new AssetsExceptionUpload('You may upload files with :ext extensions only', [
            ':ext' => implode(', ', $allowedExtensions),
        ]);
    }

    /**
     * Returns asset`s base deploy directory
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     * @throws AssetsProviderException
     */
    protected function getItemDeployPath(AssetsModelInterface $model)
    {
        $modelUrl = $model->getUrl();

        if (!$modelUrl) {
            throw new AssetsProviderException('Model must have url');
        }

        $options = [
            'provider' => $this->getUrlKey(),
            'item_url' => $modelUrl,
        ];

        // TODO remove dependency on Route
        $url = Route::url('assets-provider-item-deploy-directory', $options);

        $path = parse_url($url, PHP_URL_PATH);

        return $this->getDocRoot().DIRECTORY_SEPARATOR.ltrim($path, '/');
    }

    protected function getDocRoot()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * Removes all deployed versions of provided asset
     *
     * @param AssetsModelInterface $model
     */
    protected function dropDeployCache(AssetsModelInterface $model)
    {
        $path = $this->getItemDeployPath($model);

        if (!file_exists($path)) {
            return;
        }

        // Remove all versions of file
        foreach (glob("{$path}/*") as $file) {
            unlink($file);
        }

        // Remove directory itself
        rmdir($path);
    }

    protected function getUser()
    {
        return $this->user;
    }

    /**
     * Returns list of allowed MIME-types (or TRUE if all MIMEs are allowed)
     *
     * @return array|TRUE
     */
    abstract public function getAllowedMimeTypes();

    /**
     * Returns concrete storage for current provider
     *
     * @return AbstractAssetsStorage
     */
    abstract protected function createStorage();

    /**
     * Creates empty file model
     *
     * @return AssetsModelInterface
     */
    abstract public function createFileModel();

    /**
     * Returns TRUE if upload is granted
     *
     * @return bool
     */
    abstract protected function checkUploadPermissions();

    /**
     * Returns TRUE if deploy is granted
     *
     * @param AssetsModelInterface $model
     *
     * @return bool
     */
    abstract protected function checkDeployPermissions($model);

    /**
     * Returns TRUE if delete operation granted
     *
     * @param AssetsModelInterface $model
     *
     * @return bool
     */
    abstract protected function checkDeletePermissions($model);

    /**
     * Returns TRUE if store is granted
     *
     * @return bool
     */
    protected function checkStorePermissions()
    {
        return $this->checkUploadPermissions();
    }
}
