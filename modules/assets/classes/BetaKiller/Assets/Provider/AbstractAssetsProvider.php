<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Acl\Resource\AssetsAclResourceInterface;
use BetaKiller\Assets\AssetsDeploymentService;
use BetaKiller\Assets\ContentTypes;
use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Exception\AssetsModelException;
use BetaKiller\Assets\Exception\AssetsProviderException;
use BetaKiller\Assets\Exception\AssetsUploadException;
use BetaKiller\Assets\Exception\DuplicateFileUploadException;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Model\HashBasedAssetsModelInterface;
use BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface;
use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Config\AssetsConfig;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\IdentityConverterInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\HashStrategyAssetsRepositoryInterface;
use BetaKiller\Repository\RepositoryInterface;
use DateTimeImmutable;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Spotman\Acl\AclInterface;

use function basename;
use function dirname;
use function in_array;
use function is_array;
use function mb_strlen;

abstract class AbstractAssetsProvider implements AssetsProviderInterface
{
    public const CONFIG_MODEL_UPLOAD_KEY   = 'upload';
    public const CONFIG_MODEL_MAX_SIZE_KEY = 'max-size';

    private const SIZE_LETTERS = 'bkmgtpezy';

    /**
     * @var string
     */
    protected $codename;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \BetaKiller\Assets\Storage\AssetsStorageInterface
     */
    private $storage;

    /**
     * @var \BetaKiller\Repository\RepositoryInterface
     */
    private $repository;

    /**
     * @var \BetaKiller\Factory\EntityFactoryInterface
     */
    private $entityFactory;

    /**
     * @var \BetaKiller\Config\AssetsConfig
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
     * @var \BetaKiller\Assets\AssetsDeploymentService
     */
    private $deploymentService;

    /**
     * @var \BetaKiller\Assets\ContentTypes
     */
    private $contentTypes;

    /**
     * @var IdentityConverterInterface
     */
    protected $converter;

    /**
     * AbstractAssetsProvider constructor.
     *
     * @param \Spotman\Acl\AclInterface                                   $acl
     * @param \BetaKiller\Factory\EntityFactoryInterface                  $entityFactory
     * @param \BetaKiller\Repository\RepositoryInterface                  $repository
     * @param \BetaKiller\Assets\Storage\AssetsStorageInterface           $storage
     * @param \BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface $pathStrategy
     * @param \BetaKiller\Config\AssetsConfig                             $config
     * @param \BetaKiller\Assets\AssetsDeploymentService                  $deploymentService
     * @param \BetaKiller\Assets\ContentTypes                             $contentTypes
     * @param \BetaKiller\IdentityConverterInterface                      $converter
     * @param \Psr\Log\LoggerInterface                                    $logger
     */
    public function __construct(
        AclInterface $acl,
        EntityFactoryInterface $entityFactory,
        RepositoryInterface $repository,
        AssetsStorageInterface $storage,
        AssetsPathStrategyInterface $pathStrategy,
        AssetsConfig $config,
        AssetsDeploymentService $deploymentService,
        ContentTypes $contentTypes,
        IdentityConverterInterface $converter,
        LoggerInterface $logger
    ) {
        $this->acl               = $acl;
        $this->repository        = $repository;
        $this->entityFactory     = $entityFactory;
        $this->storage           = $storage;
        $this->pathStrategy      = $pathStrategy;
        $this->config            = $config;
        $this->deploymentService = $deploymentService;
        $this->contentTypes      = $contentTypes;
        $this->converter         = $converter;
        $this->logger            = $logger;
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
        return $this->storage->isInsideDocRoot() && $this->isProtected();
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

    private function getBaseUrl(): UriInterface
    {
        $uri = $this->config->getBaseUri();

        $path = $uri->getPath().'/'.$this->getUrlKey();

        return $uri->withPath($path);
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
        if (!$this->isCreateAllowed($user)) {
            throw new AssetsProviderException('Store is not allowed');
        }

        if (!is_file($fullPath) || !is_readable($fullPath)) {
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

        // Process hash-based assets
        if ($model instanceof HashBasedAssetsModelInterface) {
            // Check repository type
            if (!$this->repository instanceof HashStrategyAssetsRepositoryInterface) {
                throw new AssetsProviderException('Repository ":name" must implement :must', [
                    ':name' => $model::getModelName(),
                    ':must' => HashStrategyAssetsRepositoryInterface::class,
                ]);
            }

            // Calculate hash for processed content
            $hash = $this->config->isDuplicateAllowed($this->getCodename())
                // Use random hash and allow files to be uploaded multiple times
                ? $this->calculateContentHash(\random_int(1, \PHP_INT_MAX).\microtime())
                : $this->calculateContentHash($content);

            // Check for duplicates
            if ($this->repository->findByHash($hash)) {
                throw new DuplicateFileUploadException(null, [
                    ':provider' => $this->getCodename(),
                    ':hash'     => $hash,
                ]);
            }

            $model->setHash($hash);
        }

        $currentTime = new DateTimeImmutable;

        // Put data into model
        $model
            ->setOriginalName($originalName)
            ->setSize(mb_strlen($content))
            ->setMime($mimeType)
            ->setUploadedBy($user)
            ->setUploadedAt($currentTime)
            ->setLastModifiedAt($currentTime);

        // Place the file into the storage
        $this->setContent($model, $content);

        // Deploy original file if needed
        $this->deploymentService->deploy($this, $model, $content, self::ACTION_ORIGINAL);

        return $model;
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
     * @param AssetsModelInterface            $model
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function delete(AssetsModelInterface $model, UserInterface $user): void
    {
        // Check permissions
        if (!$this->isDeleteAllowed($user, $model)) {
            throw new AssetsProviderException('Delete is not allowed');
        }

        try {
            $path = $this->getOriginalPath($model);

            // Remove file from storage
            $this->storage->deleteFile($path);

            // Drop cached files
            $this->dropStorageCache($model, false);

            // Drop deployed public files
            $this->deploymentService->clear($this, $model);
        } catch (\Throwable  $e) {
            LoggerHelper::logUserException($this->logger, $e, $user);
        }

        // Remove model from repository
        $this->repository->delete($model);
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
     * @inheritDoc
     */
    public function getCachedContent(
        AssetsModelInterface $model,
        string $action,
        ?string $suffix = null
    ): ?string {
        if (!$this->isCachingEnabled()) {
            return null;
        }

        if ($action === self::ACTION_ORIGINAL) {
            // No caching of original action
            return null;
        }

        // Skip unknown actions
        if (!$this->hasAction($action)) {
            return null;
        }

        $path = $this->getModelActionPath($model, $action, $suffix);

        return $this->storage->hasFile($path)
            ? $this->storage->getFile($path)
            : null;
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
        return in_array($action, $this->getActions(), true);
    }

    /**
     * Returns TRUE if MIME-type is allowed in current provider
     *
     * @param string $mime MIME-type
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsUploadException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    private function checkAllowedMimeTypes(string $mime): bool
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
        $name  = $this->codename;
        $model = $this->entityFactory->create($name);

        if (!$model instanceof AssetsModelInterface) {
            throw new AssetsModelException('Assets model ":name" must implement :must', [
                ':name' => $name,
                ':must' => AssetsModelInterface::class,
            ]);
        }

        return $model;
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
        $basePath = str_replace('/', DIRECTORY_SEPARATOR, $this->getBaseUrl()->getPath());

        return $basePath.DIRECTORY_SEPARATOR.$this->getModelActionPath($model, $action, $suffix);
    }

    /**
     * Returns TRUE if upload is granted
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function isUploadAllowed(UserInterface $user): bool
    {
        return $this->getAclResource($user)->isUploadAllowed();
    }

    /**
     * Returns TRUE if store is granted
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function isCreateAllowed(UserInterface $user): bool
    {
        return $this->getAclResource($user)->isCreateAllowed();
    }

    /**
     * Returns TRUE if read is granted for provided model
     *
     * @param \BetaKiller\Model\UserInterface               $user
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function isReadAllowed(UserInterface $user, AssetsModelInterface $model): bool
    {
        return $this->getAclResource($user)->setEntity($model)->isReadAllowed();
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
    public function isDeleteAllowed(UserInterface $user, AssetsModelInterface $model): bool
    {
        return $this->getAclResource($user)->setEntity($model)->isDeleteAllowed();
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string[]
     */
    public function getInfo(AssetsModelInterface $model): array
    {
        return [
            AssetsModelInterface::API_KEY_ID            => $this->converter->encode($model),
            AssetsModelInterface::API_KEY_SIZE          => $model->getSize(),
            AssetsModelInterface::API_KEY_ORIGINAL_NAME => $model->getOriginalName(),
            AssetsModelInterface::API_KEY_ORIGINAL_URL  => $this->getOriginalUrl($model),
            AssetsModelInterface::API_KEY_DELETE_URL    => $this->getDeleteUrl($model),
        ];
    }

    /**
     * Returns a file size limit in bytes based on the PHP upload_max_filesize and post_max_size
     *
     * @return int
     * @see https://stackoverflow.com/a/25370978
     */
    public function getUploadMaxSize(): int
    {
        static $maxSize = -1;

        if ($maxSize < 0) {
            // Start with post_max_size.
            $postMaxSize = $this->parseIniSize(ini_get('post_max_size'));
            if ($postMaxSize > 0) {
                $maxSize = $postMaxSize;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $uploadMax = $this->parseIniSize(ini_get('upload_max_filesize'));
            if ($uploadMax > 0 && $uploadMax < $maxSize) {
                $maxSize = $uploadMax;
            }
        }

        $configSize = $this->getConfigUploadMaxSize();

        if ($configSize > 0 && $configSize < $maxSize) {
            $maxSize = $configSize;
        }

        return $maxSize;
    }

    private function parseIniSize(string $value): int
    {
        // Remove the non-unit characters from the size.
        $unit = preg_replace('/[^'.self::SIZE_LETTERS.']/i', '', $value);

        // Remove the non-numeric characters from the size.
        $size = preg_replace('/[^0-9.]/', '', $value);

        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * (1024 ** stripos(self::SIZE_LETTERS, $unit[0])));
        }

        return (int)round($size);
    }

    /**
     * @return int
     */
    private function getConfigUploadMaxSize(): ?int
    {
        $size = $this->config->getProviderConfigValue($this, [
            self::CONFIG_MODEL_UPLOAD_KEY,
            self::CONFIG_MODEL_MAX_SIZE_KEY,
        ], true);

        return $size ? $this->parseIniSize($size) : null;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function calculateContentHash(string $content): string
    {
        return sha1($content);
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

        $path     = dirname($originalPath);
        $fileName = basename($originalPath);

        foreach ($this->storage->getFiles($path) as $file) {
            if ($keepOriginal && basename($file) === $fileName) {
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
