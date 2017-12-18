<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\Handler\AssetsHandlerInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RepositoryInterface;
use Request;

interface AssetsProviderInterface
{
    public function setCodename(string $codename): void;

    /**
     * @param \BetaKiller\Assets\Handler\AssetsHandlerInterface $handler
     */
    public function addPostUploadHandler(AssetsHandlerInterface $handler): void;

    /**
     * Returns URL for POSTing new files
     *
     * @return string
     */
    public function getUploadUrl(): string;

    public function getUrlKey(): string;

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

    public function getModelExtension(AssetsModelInterface $model): string;

    /**
     * @param array                           $_file    Item from $_FILES
     * @param array                           $postData Array with items from $_POST
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return AssetsModelInterface
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    public function upload(array $_file, array $postData, UserInterface $user): AssetsModelInterface;

    /**
     * @param string                          $fullPath
     * @param string                          $originalName
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function store(string $fullPath, string $originalName, UserInterface $user): AssetsModelInterface;

    public function deploy(Request $request, AssetsModelInterface $model, string $content): bool;

    /**
     * @param AssetsModelInterface $model
     *
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    public function delete(AssetsModelInterface $model): void;

    public function saveModel(AssetsModelInterface $model): void;

    /**
     * Returns asset file model with provided hash
     *
     * @param $url
     *
     * @return AssetsModelInterface
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    public function getModelByDeployUrl(string $url): AssetsModelInterface;

    /**
     * Returns content of the file
     *
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function getContent(AssetsModelInterface $model): string;

    /**
     * Update content of the file
     *
     * @param AssetsModelInterface $model
     * @param string               $content
     */
    public function setContent(AssetsModelInterface $model, string $content): void;

    /**
     * Returns TRUE if MIME-type is allowed in current provider
     *
     * @param string $mime MIME-type
     *
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @return bool
     */
    public function checkAllowedMimeTypes(string $mime): bool;

    public function getRepository(): RepositoryInterface;

    /**
     * Returns list of allowed MIME-types (or TRUE if all MIMEs are allowed)
     *
     * @return array|TRUE
     */
    public function getAllowedMimeTypes();
}
