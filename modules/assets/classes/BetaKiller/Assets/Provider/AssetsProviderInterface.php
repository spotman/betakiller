<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\Handler\AssetsHandlerInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RepositoryInterface;
use Psr\Http\Message\UploadedFileInterface;

interface AssetsProviderInterface
{
    public const ACTION_ORIGINAL = 'original';
    public const ACTION_UPLOAD   = 'upload';
    public const ACTION_DOWNLOAD = 'download';
    public const ACTION_DELETE   = 'delete';

    /**
     * Save codename after initialization
     *
     * @param string $codename
     */
    public function setCodename(string $codename): void;

    /**
     * Returns provider`s codename
     *
     * @return string
     */
    public function getCodename(): string;

    /**
     * Returns true if current provider has protected content (no caching in public directory)
     *
     * @return bool
     */
    public function isProtected(): bool;

    /**
     * Returns true if current provider needs deployment to public directory
     *
     * @return bool
     */
    public function isDeploymentNeeded(): bool;

    /**
     * Returns true if current provider allows caching of actions` data in storage
     *
     * @return bool
     */
    public function isCachingEnabled(): bool;

    /**
     * Returns array of allowed actions` names
     *
     * @return string[]
     */
    public function getActions(): array;

    /**
     * Returns true if provider action is allowed
     *
     * @param string $action
     *
     * @return bool
     */
    public function hasAction(string $action): bool;

    /**
     * @param \BetaKiller\Assets\Handler\AssetsHandlerInterface $handler
     */
    public function addPostUploadHandler(AssetsHandlerInterface $handler): void;

    /**
     * Returns provider`s URL key or codename if no key was defined
     *
     * @return string
     */
    public function getUrlKey(): string;

    /**
     * Returns URL for POSTing new files
     *
     * @return string
     */
    public function getUploadUrl(): string;

    /**
     * Returns public original URL for provided model
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function getOriginalUrl(AssetsModelInterface $model): string;

    /**
     * Returns public download URL for provided model
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     */
    public function getDownloadUrl(AssetsModelInterface $model): string;

    /**
     * Returns URL for deleting provided file
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function getDeleteUrl(AssetsModelInterface $model): string;

    /**
     * Returns path for deployed file, relative to document root
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $action
     * @param null|string                                   $suffix
     *
     * @return string
     */
    public function getDeployRelativePath(AssetsModelInterface $model, string $action, ?string $suffix = null): string;

    /**
     * Returns file extension for provided model (like "jpeg" or "pdf")
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     */
    public function getModelExtension(AssetsModelInterface $model): string;

    /**
     * Process uploaded file
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file
     * @param array                                   $postData Array with items from $_POST
     * @param \BetaKiller\Model\UserInterface         $user
     *
     * @return AssetsModelInterface
     */
    public function upload(UploadedFileInterface $file, array $postData, UserInterface $user): AssetsModelInterface;

    /**
     * Store regular file
     * This method mainly used for importing existing files
     *
     * @param string                          $fullPath
     * @param string                          $originalName
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function store(string $fullPath, string $originalName, UserInterface $user): AssetsModelInterface;

    /**
     * @param AssetsModelInterface            $model
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function delete(AssetsModelInterface $model, UserInterface $user): void;

    /**
     * Proxy for saving model (provider already has right repository inside)
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Exception\ValidationException
     */
    public function saveModel(AssetsModelInterface $model): void;

    /**
     * Returns asset file model with provided hash
     *
     * @param $url
     *
     * @return AssetsModelInterface
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    public function getModelByPublicUrl(string $url): AssetsModelInterface;

    /**
     * Returns content of the file
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function getContent(AssetsModelInterface $model): string;

    /**
     * Save action content into storage
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $content
     * @param string                                        $action
     * @param null|string                                   $suffix
     *
     * @return void
     */
    public function cacheContent(
        AssetsModelInterface $model,
        string $content,
        string $action,
        ?string $suffix = null
    ): void;

    /**
     * Returns assets model repository linked to current provider
     *
     * @return \BetaKiller\Repository\RepositoryInterface
     */
    public function getRepository(): RepositoryInterface;

    /**
     * Returns list of allowed MIME-types (or TRUE if all MIMEs are allowed)
     *
     * @return array|TRUE
     */
    public function getAllowedMimeTypes();

    /**
     * Returns TRUE if upload is granted
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function isUploadAllowed(UserInterface $user): bool;

    /**
     * Returns TRUE if store is granted
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function isCreateAllowed(UserInterface $user): bool;

    /**
     * Returns TRUE if delete operation granted
     *
     * @param \BetaKiller\Model\UserInterface $user
     * @param AssetsModelInterface            $model
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function isDeleteAllowed(UserInterface $user, AssetsModelInterface $model): bool;

    /**
     * Returns a file size limit in bytes based on the PHP upload_max_filesize and post_max_size
     *
     * @return int
     */
    public function getUploadMaxSize(): int;

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string[]
     */
    public function getInfo(AssetsModelInterface $model): array;
}
