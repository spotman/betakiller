<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Acl\Resource\AssetsAclResourceInterface;
use BetaKiller\Assets\AssetsException;
use BetaKiller\Assets\AssetsExceptionUpload;
use BetaKiller\Assets\AssetsProviderException;
use BetaKiller\Assets\Handler\AssetsHandlerInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface;
use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RepositoryInterface;
use DateTime;
use File;
use Request;
use Upload;

abstract class AbstractAssetsProvider implements AssetsProviderInterface
{
    public const CONFIG_KEY                     = 'assets';
    public const CONFIG_URL_PATH_KEY            = 'url_path';
    public const CONFIG_PROVIDERS_KEY           = 'providers';
    public const CONFIG_STORAGES_KEY            = 'storages';
    public const CONFIG_MODEL_URL_KEY           = 'url_key';
    public const CONFIG_MODEL_PROVIDER_KEY      = 'provider';
    public const CONFIG_MODEL_PATH_STRATEGY_KEY = 'path_strategy';
    public const CONFIG_MODEL_STORAGE_KEY       = 'storage';
    public const CONFIG_MODEL_STORAGE_NAME_KEY  = 'name';
    public const CONFIG_MODEL_STORAGE_PATH_KEY  = 'path';
    public const CONFIG_MODEL_DEPLOY_KEY        = 'deploy';
    public const CONFIG_MODEL_MIMES             = 'mimes';
    public const CONFIG_MODEL_POST_UPLOAD_KEY   = 'post_upload';

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
     * @var \BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface
     */
    private $pathStrategy;

    /**
     * @var \BetaKiller\Acl\Resource\AssetsAclResourceInterface
     */
    private $aclResource;

    /**
     * @var \BetaKiller\Assets\Handler\AssetsHandlerInterface[]
     */
    private $postUploadHandlers = [];

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    public function __construct(
        RepositoryInterface $repository,
        AssetsStorageInterface $storage,
        AssetsAclResourceInterface $aclResource,
        AssetsPathStrategyInterface $pathStrategy,
        ConfigProviderInterface $config,
        AppEnvInterface $appEnv
    ) {
        $this->repository   = $repository;
        $this->storage      = $storage;
        $this->aclResource  = $aclResource;
        $this->pathStrategy = $pathStrategy;
        $this->config       = $config;
        $this->appEnv       = $appEnv;
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
        return $this->getBaseUrl().'/upload';
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
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function getOriginalUrl(AssetsModelInterface $model): string
    {
        return $this->getItemUrl('original', $model);
    }

    /**
     * Returns public download URL for provided model
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function getDownloadUrl(AssetsModelInterface $model): string
    {
        return $this->getItemUrl('download', $model);
    }

    /**
     * Returns URL for deleting provided file
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function getDeleteUrl(AssetsModelInterface $model): string
    {
        return $this->getItemUrl('delete', $model);
    }

    /**
     * @param string                                        $action
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @param null|string                                   $suffix
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    protected function getItemUrl(string $action, AssetsModelInterface $model, ?string $suffix = null): string
    {
        return $this->getBaseUrl().'/'.$this->getModelActionPath($model, $action, '/', $suffix);
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
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Assets\AssetsException
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
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsException
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
        $mimeType = $this->getFileMimeType($fullPath);

        // MIME-type check
        $this->checkAllowedMimeTypes($mimeType);

        // Init model
        $model = $this->createFileModel();

        // Get file content
        $content = file_get_contents($fullPath);

        // Custom processing
        $content = $this->customContentProcessing($content, $model);

        // Put data into model
        $model
            ->setOriginalName($originalName)
            ->setSize(\mb_strlen($content))
            ->setMime($mimeType)
            ->setUploadedBy($user);

        // Calculate hash
        $model->setHash($this->calculateHash($content));

        $path = $this->getModelStoragePath($model);

        // Place file into storage
        $this->storage->put($path, $content);

        // Save model
        $this->saveModel($model);

        return $model;
    }

    private function calculateHash(string $content): string
    {
        return sha1($content);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function saveModel(AssetsModelInterface $model): void
    {
        $this->repository->save($model);
    }

    protected function getFileMimeType($file_path): string
    {
        // TODO Remove File class dependency
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
     *
     * @throws \BetaKiller\Repository\RepositoryException
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

    /**
     * @param \Request                                      $request
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $content
     *
     * @return bool
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    public function deploy(Request $request, AssetsModelInterface $model, string $content): bool
    {
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
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Repository\RepositoryException
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

        $path = $this->getModelStoragePath($model);

        // Remove file from storage
        $this->storage->delete($path);

        // Drop deployed cache for current asset (all files)
        $this->dropDeployCache($model, false);
    }

    /**
     * Returns asset file model with provided hash
     *
     * @param string $urlPath
     *
     * @return AssetsModelInterface
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    public function getModelByDeployUrl(string $urlPath): AssetsModelInterface
    {
        $model = $this->pathStrategy->getModelByPath($urlPath);

        if (!$model) {
            throw new AssetsProviderException('Can not find file with url = :url', [':url' => $urlPath]);
        }

        return $model;
    }

    /**
     * Returns content of the file
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function getContent(AssetsModelInterface $model): string
    {
        $path = $this->getModelStoragePath($model);

        // Get file from storage
        return $this->storage->get($path);
    }

    /**
     * Update content of the file
     *
     * @param AssetsModelInterface $model
     * @param string               $content
     *
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function setContent(AssetsModelInterface $model, string $content): void
    {
        $path = $this->getModelStoragePath($model);

        $this->storage->put($path, $content);

        $keepOriginal = !$this->storage->isDeployRequired();

        // Drop deployed cache for current asset
        $this->dropDeployCache($model, $keepOriginal);
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

        if (!\is_array($allowedMimeTypes)) {
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
            // TODO Drop File class dependency
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

    /**
     * @return \BetaKiller\Repository\RepositoryInterface
     */
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
     * @throws \BetaKiller\Assets\AssetsException
     */
    protected function getItemDeployPath(AssetsModelInterface $model): string
    {
        $url = $this->getOriginalUrl($model);

        $path = parse_url($url, PHP_URL_PATH);

        return $this->getDocRoot().DIRECTORY_SEPARATOR.ltrim($path, '/');
    }

    /**
     * @return string
     */
    private function getDocRoot(): string
    {
        return $this->appEnv->getDocRootPath();
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    private function getModelStoragePath(AssetsModelInterface $model): string
    {
        $delimiter = $this->storage->getDirectorySeparator();

        return $this->pathStrategy->makeModelPath($model, $delimiter).$delimiter.$this->getOriginalFilename($model);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $action
     * @param string                                        $delimiter
     *
     * @param null|string                                   $suffix
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    protected function getModelActionPath(
        AssetsModelInterface $model,
        string $action,
        string $delimiter,
        ?string $suffix = null
    ): string {
        $path     = $this->pathStrategy->makeModelPath($model, $delimiter);
        $filename = $this->getActionFilename($model, $action, $suffix);

        // <pathStrategy>/<action>(-<size>).<ext>
        return $path.$delimiter.$filename;
    }

    protected function getRelativePath(AssetsModelInterface $model, ?string $delimiter = null): string
    {
        $delimiter = $delimiter ?? DIRECTORY_SEPARATOR;

        // <providerKey>/<pathStrategy>
        return $this->getUrlKey().$this->pathStrategy->makeModelPath($model, $delimiter);
    }

    /**
     * @return string
     */
    protected function getUrlBasePath(): string
    {
        return $this->getAssetsConfigValue([self::CONFIG_URL_PATH_KEY]);
    }

    protected function getBaseUrl(): string
    {
        return $this->getUrlBasePath().'/'.$this->getUrlKey();
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    private function getOriginalFilename(AssetsModelInterface $model): string
    {
        return $this->getActionFilename($model, 'original');
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $action
     *
     * @param null|string                                   $suffix
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    private function getActionFilename(AssetsModelInterface $model, string $action, ?string $suffix = null): string
    {
        // <action>(-<suffix>).<ext>
        return $action.($suffix ? '-'.$suffix : '').'.'.$this->getModelExtension($model);
    }

    /**
     * Removes all deployed versions of provided asset
     *
     * @param AssetsModelInterface $model
     *
     * @param bool                 $keepOriginal
     *
     * @throws \BetaKiller\Assets\AssetsException
     */
    protected function dropDeployCache(AssetsModelInterface $model, bool $keepOriginal): void
    {
        $path = $this->getItemDeployPath($model);

        if (!file_exists($path)) {
            return;
        }

        $originalFileName = $this->getOriginalFilename($model);

        // Remove all versions of file
        foreach (glob($path.DIRECTORY_SEPARATOR.'*') as $file) {
            if ($keepOriginal && basename($file) === $originalFileName) {
                continue;
            }

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
        // No deployment for public assets storage
        if (!$this->storage->isDeployRequired()) {
            return false;
        }

        // No deployment in developing environments
        return (bool)$this->getAssetsConfigValue([self::CONFIG_MODEL_DEPLOY_KEY]);
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
