<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Acl\Resource\AssetsAclResourceInterface;
use BetaKiller\Assets\AssetsDeploymentService;
use BetaKiller\Assets\AssetsExceptionUpload;
use BetaKiller\Assets\AssetsProviderException;
use BetaKiller\Assets\ContentTypes;
use BetaKiller\Assets\Handler\AssetsHandlerInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface;
use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RepositoryInterface;
use Upload;

abstract class AbstractAssetsProvider implements AssetsProviderInterface
{
    /**
     * Global config group (config file name)
     */
    public const CONFIG_KEY = 'assets';

    /**
     * Base assets url (with domain or without)
     */
    public const CONFIG_URL_PATH_KEY = 'url_path';

    /**
     * Allow deployment
     */
    public const CONFIG_DEPLOY_KEY = 'deploy';

    /**
     * Allow caching of actions content
     */
    public const CONFIG_CACHING_ENABLED_KEY = 'cache';

    /**
     * Nested group with models` definitions
     */
    public const CONFIG_MODELS_KEY = 'models';

    /**
     * Nested group with storages` defaults
     */
    public const CONFIG_STORAGES_KEY = 'storages';

    /**
     * Provider url key (slug)
     */
    public const CONFIG_MODEL_URL_KEY = 'url_key';

    /**
     * Model`s provider codename
     */
    public const CONFIG_MODEL_PROVIDER_KEY = 'provider';

    /**
     * Model`s path strategy codename
     */
    public const CONFIG_MODEL_PATH_STRATEGY_KEY = 'path_strategy';

    /**
     * Nested model`s storage config group
     */
    public const CONFIG_MODEL_STORAGE_KEY = 'storage';

    /**
     * Model`s storage codename
     */
    public const CONFIG_MODEL_STORAGE_NAME_KEY = 'name';

    /**
     * Model`s storage path name (single level)
     */
    public const CONFIG_MODEL_STORAGE_PATH_KEY = 'path';

    /**
     * Marker for setting model as "protected" (no direct public access)
     */
    public const CONFIG_MODEL_PROTECTED_KEY = 'protected';

    /**
     * Allowed mime-types
     */
    public const CONFIG_MODEL_MIMES = 'mimes';

    /**
     * Post upload handlers list
     */
    public const CONFIG_MODEL_POST_UPLOAD_KEY = 'post_upload';

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
     * @var \BetaKiller\Assets\AssetsDeploymentService
     */
    private $deploymentService;

    /**
     * @var \BetaKiller\Assets\ContentTypes
     */
    private $contentTypes;

    /**
     * AbstractAssetsProvider constructor.
     *
     * @param \BetaKiller\Repository\RepositoryInterface                  $repository
     * @param \BetaKiller\Assets\Storage\AssetsStorageInterface           $storage
     * @param \BetaKiller\Acl\Resource\AssetsAclResourceInterface         $aclResource
     * @param \BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface $pathStrategy
     * @param \BetaKiller\Config\ConfigProviderInterface                  $config
     * @param \BetaKiller\Assets\AssetsDeploymentService                  $deploymentService
     * @param \BetaKiller\Assets\ContentTypes                             $contentTypes
     */
    public function __construct(
        RepositoryInterface $repository,
        AssetsStorageInterface $storage,
        AssetsAclResourceInterface $aclResource,
        AssetsPathStrategyInterface $pathStrategy,
        ConfigProviderInterface $config,
        AssetsDeploymentService $deploymentService,
        ContentTypes $contentTypes
    ) {
        $this->repository        = $repository;
        $this->storage           = $storage;
        $this->aclResource       = $aclResource;
        $this->pathStrategy      = $pathStrategy;
        $this->config            = $config;
        $this->deploymentService = $deploymentService;
        $this->contentTypes      = $contentTypes;
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

        return $this->getAssetsConfigValue(array_merge([self::CONFIG_MODELS_KEY, $codename], $path));
    }

    /**
     * Returns true if current provider has protected content (no caching in public directory)
     *
     * @return bool
     */
    public function isProtected(): bool
    {
        return (bool)$this->getAssetsConfigValue([self::CONFIG_MODEL_PROTECTED_KEY]);
    }

    /**
     * Returns true if current provider needs deployment to public directory
     *
     * @return bool
     */
    public function isDeploymentNeeded(): bool
    {
        // Allow env-dependent deployment disabling
        if (!$this->getAssetsConfigValue([self::CONFIG_DEPLOY_KEY])) {
            return false;
        }

        // Deployment allowed only for protected assets in public storage (like static files, located in modules)
        return $this->storage->isPublic() && $this->isProtected();
    }

    /**
     * Returns true if current provider allows caching of actions` data in storage
     *
     * @return bool
     */
    public function isCachingEnabled(): bool
    {
        return (bool)$this->getAssetsConfigValue([self::CONFIG_CACHING_ENABLED_KEY]);
    }

    public function setCodename(string $codename): void
    {
        $this->codename = $codename;
    }

    /**
     * Returns provider`s codename
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->codename;
    }

    /**
     * Returns URL for POSTing new files
     *
     * @return string
     */
    public function getUploadUrl(): string
    {
        return $this->getBaseUrl().'/'.self::ACTION_UPLOAD;
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
        return $this->getItemUrl(self::ACTION_ORIGINAL, $model);
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
        return $this->getItemUrl(self::ACTION_DOWNLOAD, $model);
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
        return $this->getItemUrl(self::ACTION_DELETE, $model);
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
        if (!$this->hasAction($action)) {
            throw new AssetsProviderException('Action :action is not allowed for provider :codename', [
                ':action'   => $action,
                ':codename' => $this->codename,
            ]);
        }

        $path = $this->getModelActionPath($model, $action, $suffix);
        $path = $this->prepareDirectorySeparator($path, '/');

        return $this->getBaseUrl().'/'.$path;
    }

    private function prepareDirectorySeparator(string $path, string $targetDirectorySeparator): string
    {
        $systemDirectorySeparator = DIRECTORY_SEPARATOR;

        if ($targetDirectorySeparator === $systemDirectorySeparator) {
            // Nothing to do
            return $path;
        }

        return str_replace($systemDirectorySeparator, $targetDirectorySeparator, $path);
    }

    /**
     * @return string
     */
    private function getUrlBasePath(): string
    {
        return $this->getAssetsConfigValue([self::CONFIG_URL_PATH_KEY]);
    }

    private function getBaseUrl(): string
    {
        return $this->getUrlBasePath().'/'.$this->getUrlKey();
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function getModelExtension(AssetsModelInterface $model): string
    {
        return $this->contentTypes->getPrimaryExtension($model->getMime());
    }

    /**
     * @param string $mimeType
     *
     * @return string[]
     * @throws \BetaKiller\Assets\AssetsException
     */
    private function getMimeExtensions(string $mimeType): array
    {
        return $this->contentTypes->getExtensions($mimeType);
    }

    /**
     * @param array                           $file     Item from $_FILES
     * @param array                           $postData Array with items from $_POST
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return AssetsModelInterface
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Assets\AssetsExceptionUpload
     */
    public function upload(array $file, array $postData, UserInterface $user): AssetsModelInterface
    {
        // Check permissions
        if (!$this->checkUploadPermissions()) {
            throw new AssetsProviderException('Upload is not allowed');
        }

        // Security checks
        if (!Upload::not_empty($file) || !Upload::valid($file)) {
            // TODO Разные сообщения об ошибках в тексте исключения (файл слишком большой, итд)
            throw new AssetsExceptionUpload('Incorrect file, upload rejected');
        }

        $fullPath     = $file['tmp_name'];
        $originalName = strip_tags($file['name']);

        $model = $this->store($fullPath, $originalName, $user);

        $this->postUploadProcessing($model, $postData);

        return $model;
    }

    /**
     * Returns asset model with predefined fields.
     * Model needs to be saved in repository after calling this method.
     *
     * @param string                          $fullPath
     * @param string                          $originalName
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
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

        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            throw new AssetsProviderException('File is not readable :path', [':path' => $fullPath]);
        }

        // Get file content
        $content = file_get_contents($fullPath);

        // Get type from file analysis
        $mimeType = $this->contentTypes->getFileMimeType($fullPath);

        // MIME-type check
        $this->checkAllowedMimeTypes($mimeType);

        // Init model
        $model = $this->createFileModel();

        // Custom processing
        $content = $this->customContentProcessing($content, $model);

        $currentTime = new \DateTimeImmutable;

        // Put data into model
        $model
            ->setOriginalName($originalName)
            ->setSize(\mb_strlen($content))
            ->setMime($mimeType)
            ->setUploadedBy($user)
            ->setUploadedAt($currentTime)
            ->setLastModifiedAt($currentTime);

        // Calculate hash
        $model->setHash($this->calculateHash($content));

        // Place file into storage
        $this->setContent($model, $content);

        // Deploy original file if needed
        $this->deploymentService->deploy($this, $model, $content, self::ACTION_ORIGINAL);

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

        $path = $this->getOriginalPath($model);

        // Remove file from storage
        $this->storage->deleteFile($path);

        // Drop cached files
        $this->dropStorageCache($model, false);

        // Drop deployed public files
        $this->deploymentService->clear($this, $model);
    }

    /**
     * Returns asset file model with provided hash
     *
     * @param string $urlPath
     *
     * @return AssetsModelInterface
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    public function getModelByPublicUrl(string $urlPath): AssetsModelInterface
    {
        // TODO Deal with full url instead of routed one
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
        $path = $this->getOriginalPath($model);

        // Get file from storage
        return $this->storage->getFile($path);
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
    private function setContent(AssetsModelInterface $model, string $content): void
    {
        $path = $this->getOriginalPath($model);

        $this->storage->putFile($path, $content);

        // Drop deployed public files for current asset
        $this->deploymentService->clear($this, $model);

        // Drop cached actions in storage
        $this->dropStorageCache($model, true);
    }

    /**
     * Save action content into storage
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $content
     * @param string                                        $action
     * @param null|string                                   $suffix
     *
     * @return void
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function cacheContent(
        AssetsModelInterface $model,
        string $content,
        string $action,
        ?string $suffix = null
    ): void {
        if (!$this->isCachingEnabled()) {
            return;
        }

        if ($action === self::ACTION_ORIGINAL) {
            // No caching of original action
            return;
        }

        // Skip unknown actions
        if (!$this->hasAction($action)) {
            return;
        }

        $path = $this->getModelActionPath($model, $action, $suffix);

        $this->storage->putFile($path, $content);
    }

    /**
     * Returns true if provider action is allowed
     *
     * @param string $action
     *
     * @return bool
     */
    public function hasAction(string $action): bool
    {
        return \in_array($action, $this->getActions(), true);
    }

    /**
     * Returns TRUE if MIME-type is allowed in current provider
     *
     * @param string $mime MIME-type
     *
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsExceptionUpload
     * @throws \BetaKiller\Assets\AssetsException
     * @return bool
     */
    private function checkAllowedMimeTypes(string $mime): bool
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
            $allowedExtensions[] = $this->getMimeExtensions($allowedMime);
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
    private function createFileModel(): AssetsModelInterface
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
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $action
     * @param null|string                                   $suffix
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function getDeployRelativePath(AssetsModelInterface $model, string $action, ?string $suffix = null): string
    {
        $basePath = parse_url($this->getBaseUrl(), PHP_URL_PATH);
        $basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);

        return $basePath.DIRECTORY_SEPARATOR.$this->getModelActionPath($model, $action, $suffix);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    private function getOriginalPath(AssetsModelInterface $model): string
    {
        return $this->getModelActionPath($model, self::ACTION_ORIGINAL);
    }

    /**
     * <pathStrategy>/<action>(-<size>).<ext>
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $action
     * @param null|string                                   $suffix
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    private function getModelActionPath(AssetsModelInterface $model, string $action, ?string $suffix = null): string
    {
        $path     = $this->pathStrategy->makeModelPath($model);
        $filename = $this->getActionFilename($model, $action, $suffix);

        return $path.DIRECTORY_SEPARATOR.$filename;
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
     * Removes all cached versions of provided asset (previews, etc)
     *
     * @param AssetsModelInterface $model
     *
     * @param bool                 $keepOriginal
     *
     * @throws \BetaKiller\Assets\AssetsException
     */
    private function dropStorageCache(AssetsModelInterface $model, bool $keepOriginal): void
    {
        if (!$this->isCachingEnabled()) {
            return;
        }

        $originalPath = $this->getOriginalPath($model);

        $path             = \dirname($originalPath);
        $originalFileName = \basename($originalPath);
        $files            = $this->storage->getFiles($path);

        foreach ($files as $file) {
            if ($keepOriginal && basename($file) === $originalFileName) {
                continue;
            }

            $this->storage->deleteFile($file);
        }

        if (!$keepOriginal) {
            // Remove directory itself
            $this->storage->deleteDirectory($path);
        }
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
    private function checkUploadPermissions(): bool
    {
        return $this->aclResource->isUploadAllowed();
    }

    /**
     * Returns TRUE if store is granted
     *
     * @return bool
     */
    private function checkStorePermissions(): bool
    {
        return $this->aclResource->isCreateAllowed();
    }

    /**
     * Returns TRUE if delete operation granted
     *
     * @param AssetsModelInterface $model
     *
     * @return bool
     */
    private function checkDeletePermissions($model): bool
    {
        return $this->aclResource->setEntity($model)->isDeleteAllowed();
    }
}
