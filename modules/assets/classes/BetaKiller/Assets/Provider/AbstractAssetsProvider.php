<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Acl\Resource\AssetsAclResourceInterface;
use BetaKiller\Assets\AssetsException;
use BetaKiller\Assets\AssetsExceptionUpload;
use BetaKiller\Assets\AssetsProviderException;
use BetaKiller\Assets\Handler\AssetsHandlerInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\MultiLevelPath;
use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Assets\UrlStrategy\AssetsUrlStrategyInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RepositoryInterface;
use DateTime;
use File;
use Request;
use Route;
use Upload;

abstract class AbstractAssetsProvider implements AssetsProviderInterface
{
    const CONFIG_KEY                    = 'assets';
    const CONFIG_DOC_ROOT_KEY           = 'doc_root';
    const CONFIG_URL_PATH_KEY           = 'url_path';
    const CONFIG_PROVIDERS_KEY          = 'providers';
    const CONFIG_STORAGES_KEY           = 'storages';
    const CONFIG_MODEL_URL_KEY          = 'url_key';
    const CONFIG_MODEL_PROVIDER_KEY     = 'provider';
    const CONFIG_MODEL_URL_STRATEGY_KEY = 'url_strategy';
    const CONFIG_MODEL_STORAGE_KEY      = 'storage';
    const CONFIG_MODEL_STORAGE_NAME_KEY = 'name';
    const CONFIG_MODEL_STORAGE_PATH_KEY = 'path';
    const CONFIG_MODEL_DEPLOY_KEY       = 'deploy';
    const CONFIG_MODEL_MIMES            = 'mimes';
    const CONFIG_MODEL_POST_UPLOAD_KEY  = 'post_upload';
    const CONFIG_STORAGE_BASE_PATH_KEY  = 'base_path';

    /**
     * @var string
     */
    protected $codename;

    /**
     * @var \BetaKiller\Assets\Storage\AssetsStorageInterface
     */
    private $storage;

    /**
     * @var \BetaKiller\Repository\RepositoryInterface
     */
    private $repository;

    /**
     * @var ConfigProviderInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Assets\UrlStrategy\AssetsUrlStrategyInterface
     */
    private $urlStrategy;

    /**
     * @var \BetaKiller\Assets\MultiLevelPath
     */
    private $multiLevelPath;

    /**
     * @var \BetaKiller\Acl\Resource\AssetsAclResourceInterface
     */
    private $aclResource;

    /**
     * @var \BetaKiller\Assets\Handler\AssetsHandlerInterface[]
     */
    private $postUploadHandlers = [];

    public function __construct(
        RepositoryInterface $repository,
        AssetsStorageInterface $storage,
        AssetsAclResourceInterface $aclResource,
        AssetsUrlStrategyInterface $urlStrategy,
        ConfigProviderInterface $config,
        MultiLevelPath $multiLevelPath
    ) {
        $this->repository     = $repository;
        $this->storage        = $storage;
        $this->aclResource    = $aclResource;
        $this->urlStrategy    = $urlStrategy;
        $this->config         = $config;
        $this->multiLevelPath = $multiLevelPath;
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

    public function setCodename(string $codename): void
    {
        $this->codename = $codename;
    }

    /**
     * Returns URL for POSTing new files
     *
     * @return string
     */
    public function getUploadUrl(): string
    {
        $options = [
            'provider' => $this->getUrlKey(),
        ];

        // TODO Remove Route dependency
        return Route::url('assets-provider-upload', $options);
    }

    public function getUrlKey(): string
    {
        return $this->getUrlKeyConfigValue() ?: $this->codename;
    }

    private function getUrlKeyConfigValue(): string
    {
        return $this->getAssetsProviderConfigValue([self::CONFIG_MODEL_URL_KEY]);
    }

    /**
     * Returns public URL for provided model
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function getOriginalUrl(AssetsModelInterface $model): string
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
    public function getDeleteUrl(AssetsModelInterface $model): string
    {
        return $this->getItemUrl('delete', $model);
    }

    protected function getItemUrl(string $action, AssetsModelInterface $model): string
    {
        $options = [
            'provider' => $this->getUrlKey(),
            'action'   => $action,
            'item_url' => $this->getModelUrlPath($model),
            'ext'      => $this->getModelExtension($model),
        ];

        return Route::url('assets-provider-item', $options);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    protected function getModelUrlPath(AssetsModelInterface $model): string
    {
        $filename = $this->urlStrategy->getFilenameFromModel($model);
        $path     = $this->multiLevelPath->make($filename, '/');

        if (!$path) {
            throw new AssetsProviderException('Model must have url');
        }

        return $path;
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function getModelExtension(AssetsModelInterface $model): string
    {
        $mime       = $model->getMime();
        $extensions = File::exts_by_mime($mime);

        if (!$extensions) {
            throw new AssetsException('MIME :mime has no defined extension', [':mime' => $mime]);
        }

        return array_pop($extensions);
    }

    /**
     * @param array                           $_file    Item from $_FILES
     * @param array                           $postData Array with items from $_POST
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return AssetsModelInterface
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Assets\AssetsExceptionUpload
     */
    public function upload(array $_file, array $postData, UserInterface $user): AssetsModelInterface
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

        $fullPath     = $_file['tmp_name'];
        $originalName = strip_tags($_file['name']);

        $model = $this->store($fullPath, $originalName, $user);

        $this->postUploadProcessing($model, $postData);

        return $model;
    }

    /**
     * @param string                          $fullPath
     * @param string                          $originalName
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsExceptionUpload
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function store(string $fullPath, string $originalName, UserInterface $user): AssetsModelInterface
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
        $content = $this->customContentProcessing($content, $model);

        // Put data into model
        $model
            ->setOriginalName($originalName)
            ->setSize(strlen($content))
            ->setMime($mime_type)
            ->setUploadedBy($user);

        // Calculate hash
        $model->setHash($this->calculateHash($content));

        // Place file into storage
        $this->storage->put($model, $content);

        // Save model
        $this->saveModel($model);

        return $model;
    }

    private function calculateHash(string $content): string
    {
        return sha1($content);
    }

    public function saveModel(AssetsModelInterface $model): void
    {
        $this->repository->save($model);
    }

    protected function getFileMimeType($file_path): string
    {
        return File::mime($file_path);
    }

    /**
     * Custom upload processing
     *
     * @param string               $content
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    protected function customContentProcessing(string $content, $model): string
    {
        // No changes by default
        return $content;
    }

    /**
     * After upload processing
     *
     * @param AssetsModelInterface $model
     * @param array                $postData
     */
    protected function postUploadProcessing($model, array $postData): void
    {
        if ($this->postUploadHandlers) {
            foreach ($this->postUploadHandlers as $handler) {
                $handler->update($this, $model, $postData);
            }

            // Save updated model if needed
            $this->saveModel($model);
        }
    }

    /**
     * @param \BetaKiller\Assets\Handler\AssetsHandlerInterface $handler
     */
    public function addPostUploadHandler(AssetsHandlerInterface $handler): void
    {
        $this->postUploadHandlers[] = $handler;
    }

    public function deploy(Request $request, AssetsModelInterface $model, string $content): bool
    {
        $deployAllowed = (bool)$this->getAssetsConfigValue(['deploy', 'enabled']);

        // No deployment in testing and developing environments
        if (!$deployAllowed) {
            return false;
        }

        // Check permissions
        if (!$this->checkDeployPermissions()) {
            return false;
        }

        // Get item base deploy path
        $path = $this->getItemDeployPath($model);

        // Create deploy path if not exists
        if (!file_exists($path) && !@mkdir($path, 0777, true) && !is_dir($path)) {
            throw new AssetsProviderException('Can not create path :value', [
                ':value' => $path,
            ]);
        }

        $filename = $this->getItemDeployFilename($request);

        // Make deploy filename
        $fulPath = $path.DIRECTORY_SEPARATOR.$filename;

        file_put_contents($fulPath, $content);

        // Update last modification time for better caching
        $lastModified = $model->getLastModifiedAt() ?: new DateTime();
        touch($fulPath, $lastModified->getTimestamp());

        return true;
    }

    protected function getItemDeployFilename(Request $request): string
    {
        return $request->action().'.'.$request->param('ext');
    }

    /**
     * @param AssetsModelInterface $model
     *
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function delete(AssetsModelInterface $model): void
    {
        // Check permissions
        if (!$this->checkDeletePermissions($model)) {
            throw new AssetsProviderException('Delete is not allowed');
        }

        // Remove model from repository
        $this->repository->delete($model);

        // Remove file from storage
        $this->storage->delete($model);

        // Drop deployed cache for current asset
        $this->dropDeployCache($model);
    }

    /**
     * Returns asset file model with provided hash
     *
     * @param $url
     *
     * @return AssetsModelInterface
     * @throws AssetsProviderException
     */
    public function getModelByDeployUrl(string $url): AssetsModelInterface
    {
        $filename = $this->multiLevelPath->parse($url);
        $model    = $this->urlStrategy->getModelFromFilename($filename);

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
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function getContent(AssetsModelInterface $model): string
    {
        // Get file from storage
        return $this->storage->get($model);
    }

    /**
     * Update content of the file
     *
     * @param AssetsModelInterface $model
     * @param string               $content
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    public function setContent(AssetsModelInterface $model, string $content): void
    {
        $this->storage->put($model, $content);

        // Drop deployed cache for current asset
        $this->dropDeployCache($model);
    }

    /**
     * Returns TRUE if MIME-type is allowed in current provider
     *
     * @param string $mime MIME-type
     *
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsExceptionUpload
     * @return bool
     */
    public function checkAllowedMimeTypes(string $mime): bool
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
            $allowedExtensions[] = File::exts_by_mime($allowedMime);
        }

        throw new AssetsExceptionUpload('You may upload files with :ext extensions only', [
            ':ext' => implode(', ', array_merge(...$allowedExtensions)),
        ]);
    }

    /**
     * Creates empty file model
     *
     * @return AssetsModelInterface
     */
    public function createFileModel(): AssetsModelInterface
    {
        return $this->repository->create();
    }

    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Returns asset`s base deploy directory
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     * @throws AssetsProviderException
     */
    protected function getItemDeployPath(AssetsModelInterface $model): string
    {
        $options = [
            'provider' => $this->getUrlKey(),
            'item_url' => $this->getModelUrlPath($model),
        ];

        // TODO remove dependency on Route
        $url = Route::url('assets-provider-item-deploy-directory', $options);

        $path = parse_url($url, PHP_URL_PATH);

        return $this->getDocRoot().DIRECTORY_SEPARATOR.ltrim($path, '/');
    }

    /**
     * @return string
     */
    private function getDocRoot(): string
    {
        return $this->getAssetsConfigValue([self::CONFIG_DOC_ROOT_KEY]) ?: $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * @return string
     * @todo Use this instead of Route
     */
    private function getUrlPath(): string
    {
        return $this->getAssetsConfigValue([self::CONFIG_URL_PATH_KEY]);
    }

    /**
     * Removes all deployed versions of provided asset
     *
     * @param AssetsModelInterface $model
     *
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    protected function dropDeployCache(AssetsModelInterface $model): void
    {
        $path = $this->getItemDeployPath($model);

        if (!file_exists($path)) {
            return;
        }

        // Remove all versions of file
        foreach (glob($path.DIRECTORY_SEPARATOR.'*') as $file) {
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
    public function getAllowedMimeTypes()
    {
        return $this->getAssetsProviderConfigValue([self::CONFIG_MODEL_MIMES]);
    }

    /**
     * Returns TRUE if upload is granted
     *
     * @return bool
     */
    protected function checkUploadPermissions(): bool
    {
        return $this->aclResource->isUploadAllowed();
    }

    /**
     * Returns TRUE if store is granted
     *
     * @return bool
     */
    protected function checkStorePermissions(): bool
    {
        return $this->aclResource->isCreateAllowed();
    }

    /**
     * Returns TRUE if deploy is granted
     *
     * @return bool
     */
    protected function checkDeployPermissions(): bool
    {
        return (bool)$this->getAssetsProviderConfigValue([self::CONFIG_MODEL_DEPLOY_KEY]);
    }

    /**
     * Returns TRUE if delete operation granted
     *
     * @param AssetsModelInterface $model
     *
     * @return bool
     */
    protected function checkDeletePermissions($model): bool
    {
        return $this->aclResource->setEntity($model)->isDeleteAllowed();
    }
}
