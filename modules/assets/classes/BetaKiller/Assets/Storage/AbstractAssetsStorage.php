<?php
namespace BetaKiller\Assets\Storage;

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

    private function makeFullPath(string $relativePath): string
    {
        return $this->basePath
            ? $this->basePath.DIRECTORY_SEPARATOR.$relativePath
            : $relativePath;
    }

    /**
     * @param string $path
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function get(string $path): string
    {
        $path = $this->makeFullPath($path);

        return $this->doGet($path);
    }

    /**
     * Stores file
     *
     * @param string $path
     * @param string $content
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function put(string $path, string $content): void
    {
        $path = $this->makeFullPath($path);

        $this->doPut($path, $content);
    }

    /**
     * Deletes the file
     *
     * @param string $path
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function delete(string $path): void
    {
        $path = $this->makeFullPath($path);

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
