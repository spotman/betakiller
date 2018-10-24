<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Acl\Resource\AssetsAclResourceInterface;
use BetaKiller\Assets\AssetsConfig;
use BetaKiller\Assets\AssetsDeploymentService;
use BetaKiller\Assets\ContentTypes;
use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Exception\AssetsProviderException;
use BetaKiller\Assets\Exception\AssetsUploadException;
use BetaKiller\Assets\Exception\CantWriteUploadException;
use BetaKiller\Assets\Exception\ExtensionHaltedUploadException;
use BetaKiller\Assets\Exception\FormSizeUploadException;
use BetaKiller\Assets\Exception\IniSizeUploadException;
use BetaKiller\Assets\Exception\NoFileUploadException;
use BetaKiller\Assets\Exception\NoTmpDirUploadException;
use BetaKiller\Assets\Exception\PartialUploadException;
use BetaKiller\Assets\Handler\AssetsHandlerInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface;
use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RepositoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spotman\Acl\AclInterface;

abstract class AbstractAssetsProvider implements AssetsProviderInterface
{
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
     * @var \BetaKiller\Assets\AssetsConfig
     */
    protected $config;

    /**
     * @var \BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface
     */
    private $pathStrategy;

    /**
     * @var AclInterface
     */
    private $acl;

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
     * @param \Spotman\Acl\AclInterface                                   $acl
     * @param \BetaKiller\Repository\RepositoryInterface                  $repository
     * @param \BetaKiller\Assets\Storage\AssetsStorageInterface           $storage
     * @param \BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface $pathStrategy
     * @param \BetaKiller\Assets\AssetsConfig                             $config
     * @param \BetaKiller\Assets\AssetsDeploymentService                  $deploymentService
     * @param \BetaKiller\Assets\ContentTypes                             $contentTypes
     */
    public function __construct(
        AclInterface $acl,
        RepositoryInterface $repository,
        AssetsStorageInterface $storage,
        AssetsPathStrategyInterface $pathStrategy,
        AssetsConfig $config,
        AssetsDeploymentService $deploymentService,
        ContentTypes $contentTypes
    ) {
        $this->acl               = $acl;
        $this->repository        = $repository;
        $this->storage           = $storage;
        $this->pathStrategy      = $pathStrategy;
        $this->config            = $config;
        $this->deploymentService = $deploymentService;
        $this->contentTypes      = $contentTypes;
    }

    /**
     * Returns true if current provider has protected content (no caching in public directory)
     *
     * @return bool
     */
    public function isProtected(): bool
    {
        return $this->config->isProtected($this);
    }

    /**
     * Returns true if current provider needs deployment to public directory
     *
     * @return bool
     */
    public function isDeploymentNeeded(): bool
    {
        // Allow env-dependent deployment disabling
        if (!$this->config->isDeploymentEnabled()) {
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
        return $this->config->isCachingEnabled();
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
        return $this->config->getUrlKey($this);
    }

    /**
     * Returns public URL for provided model
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
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
        return $this->config->getUrlPath();
    }

    private function getBaseUrl(): string
    {
        return $this->getUrlBasePath().'/'.$this->getUrlKey();
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function getModelExtension(AssetsModelInterface $model): string
    {
        return $this->contentTypes->getPrimaryExtension($model->getMime());
    }

    /**
     * @param string $mimeType
     *
     * @return string[]
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    private function getMimeExtensions(string $mimeType): array
    {
        return $this->contentTypes->getExtensions($mimeType);
    }

    /**
     * @param \Psr\Http\Message\UploadedFileInterface $file     Item from $_FILES
     * @param array                                   $postData Array with items from $_POST
     * @param \BetaKiller\Model\UserInterface         $user
     *
     * @return AssetsModelInterface
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsUploadException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function upload(UploadedFileInterface $file, array $postData, UserInterface $user): AssetsModelInterface
    {
        // Check permissions
        if (!$this->checkUploadPermissions($user)) {
            throw new AssetsProviderException('Upload is not allowed');
        }

        // Security checks
        $this->checkUploadedFile($file);

        // Move uploaded file to temp location (and proceed underlying checks)
        $fullPath = \tempnam(\sys_get_temp_dir(), 'assets-upload');
        $file->moveTo($fullPath);

        // Store temp file
        $name  = strip_tags($file->getClientFilename());
        $model = $this->store($fullPath, $name, $user);

        $this->postUploadProcessing($model, $postData);

        // Cleanup
        unlink($fullPath);

        return $model;
    }

    private function checkUploadedFile(UploadedFileInterface $file): void
    {
        if (!$file->getSize()) {
            throw new NoFileUploadException;
        }

        // TODO i18n for exceptions
        switch ($file->getError()) {
            case \UPLOAD_ERR_OK:
                return;

            case \UPLOAD_ERR_CANT_WRITE:
                throw new CantWriteUploadException;

            case \UPLOAD_ERR_NO_TMP_DIR:
                throw new NoTmpDirUploadException;

            case \UPLOAD_ERR_EXTENSION:
                throw new ExtensionHaltedUploadException;

            case \UPLOAD_ERR_FORM_SIZE:
                throw new FormSizeUploadException;

            case \UPLOAD_ERR_INI_SIZE:
                throw new IniSizeUploadException;

            case \UPLOAD_ERR_NO_FILE:
                throw new NoFileUploadException;

            case \UPLOAD_ERR_PARTIAL:
                throw new PartialUploadException;

            default:
                throw new AssetsUploadException;
        }
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
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsUploadException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function store(string $fullPath, string $originalName, UserInterface $user): AssetsModelInterface
    {
        // Check permissions
        if (!$this->checkStorePermissions($user)) {
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
     * @param AssetsModelInterface            $model
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function delete(AssetsModelInterface $model, UserInterface $user): void
    {
        // Check permissions
        if (!$this->checkDeletePermissions($user, $model)) {
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
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
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
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Assets\Exception\AssetsUploadException
     * @throws \BetaKiller\Assets\Exception\AssetsException
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

        throw new AssetsUploadException('You may upload files with :ext extensions only', [
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
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
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    private function dropStorageCache(AssetsModelInterface $model, bool $keepOriginal): void
    {
        if (!$this->isCachingEnabled()) {
            return;
        }

        $originalPath = $this->getOriginalPath($model);

        $path             = \dirname($originalPath);
        $originalFileName = \basename($originalPath);
        foreach ($this->storage->getFiles($path) as $file) {
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
        return $this->config->getAllowedMimeTypes($this);
    }

    /**
     * Returns TRUE if upload is granted
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    private function checkUploadPermissions(UserInterface $user): bool
    {
        $resource = $this->getAclResource($user);

        return $resource->isUploadAllowed();
    }

    /**
     * Returns TRUE if store is granted
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    private function checkStorePermissions(UserInterface $user): bool
    {
        $resource = $this->getAclResource($user);

        return $resource->isCreateAllowed();
    }

    /**
     * Returns TRUE if delete operation granted
     *
     * @param \BetaKiller\Model\UserInterface $user
     * @param AssetsModelInterface            $model
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    private function checkDeletePermissions(UserInterface $user, $model): bool
    {
        $resource = $this->getAclResource($user);

        return $resource->setEntity($model)->isDeleteAllowed();
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Acl\Resource\AssetsAclResourceInterface
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    private function getAclResource(UserInterface $user): AssetsAclResourceInterface
    {
        // Acl resource name is equal to model name
        $codename = $this->getCodename();

        $resource = $this->acl->getResource($codename);

        if (!($resource instanceof AssetsAclResourceInterface)) {
            throw new AssetsException('Acl resource :name must implement :must', [
                ':name' => $this->codename,
                ':must' => AssetsAclResourceInterface::class,
            ]);
        }

        $this->acl->injectUserResolver($user, $resource);

        return $resource;
    }
}
