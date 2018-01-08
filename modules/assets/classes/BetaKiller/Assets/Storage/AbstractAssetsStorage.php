<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\Model\AssetsModelInterface;

/**
 * Class AbstractAssetsStorage
 * Abstract storage for assets
 */
abstract class AbstractAssetsStorage implements AssetsStorageInterface
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     */
    protected function getModelFullPath(AssetsModelInterface $model): string
    {
        $relativePath = $this->getModelRelativePath($model);

        return $this->makeFullPath($relativePath);
    }

    protected function getModelRelativePath(AssetsModelInterface $model): string
    {
        return $model->getStorageFileName();
    }

    private function makeFullPath(string $relativePath): string
    {
        return $this->basePath
            ? $this->basePath.DIRECTORY_SEPARATOR.$relativePath
            : $relativePath;
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function get(AssetsModelInterface $model): string
    {
        $path = $this->getModelFullPath($model);

        return $this->doGet($path);
    }

    /**
     * Stores file
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $content
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function put(AssetsModelInterface $model, string $content): void
    {
        $path = $this->getModelFullPath($model);

        $this->doPut($path, $content);
    }

    /**
     * Deletes the file
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function delete(AssetsModelInterface $model): void
    {
        $path = $this->getModelFullPath($model);

        $this->doDelete($path);
    }

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    abstract protected function doGet(string $path): string;

    /**
     * Creates the file or updates its content
     *
     * @param string $path    Local path to file in current storage
     * @param string $content String content of the file
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    abstract protected function doPut(string $path, string $content): void;

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return bool
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    abstract protected function doDelete(string $path): bool;
}
