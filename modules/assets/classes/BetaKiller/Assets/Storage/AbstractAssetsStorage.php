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
    private string $basePath;

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    private function makeFullPath(string $relativePath): string
    {
        $fullPath = $this->basePath
            ? $this->basePath.DIRECTORY_SEPARATOR.$relativePath
            : $relativePath;

        $ds = $this->getDirectorySeparator();

        // Prepare path (replace directory separator if needed)
        if ($ds !== DIRECTORY_SEPARATOR) {
            $fullPath = str_replace(DIRECTORY_SEPARATOR, $ds, $fullPath);
        }

        return $fullPath;
    }

    /**
     * @param string $path
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function getFile(string $path): string
    {
        $path = $this->makeFullPath($path);

        return $this->doGetFile($path);
    }

    /**
     * @inheritDoc
     */
    public function hasFile(string $path): bool
    {
        $path = $this->makeFullPath($path);

        return $this->doHasFile($path);
    }

    /**
     * Stores file
     *
     * @param string $path
     * @param string $content
     *
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function putFile(string $path, string $content): void
    {
        $path = $this->makeFullPath($path);

        $this->doPutFile($path, $content);
    }

    /**
     * Deletes the file
     *
     * @param string $path
     *
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function deleteFile(string $path): void
    {
        $path = $this->makeFullPath($path);

        $this->doDeleteFile($path);
    }

    /**
     * Returns array of files in provided directory
     *
     * @param string $directory
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function getFiles(string $directory): array
    {
        $path = $this->makeFullPath($directory);

        $files = [];

        foreach ($this->doGetFiles($path) as $file) {
            // Strip base path
            $files[] = \str_replace($this->basePath, '', $file);
        }

        return $files;
    }

    /**
     * Delete provided directory. Throws an exception if there are files inside
     *
     * @param string $path
     *
     * @return void
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function deleteDirectory(string $path): void
    {
        $path = $this->makeFullPath($path);

        $this->doDeleteDirectory($path);
    }

    /**
     * Returns directory separator used in this storage
     *
     * @return string
     */
    abstract protected function getDirectorySeparator(): string;

    /**
     * Returns true if file exists
     *
     * @param string $path Local path in storage
     *
     * @return bool
     */
    abstract protected function doHasFile(string $path): bool;

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    abstract protected function doGetFile(string $path): string;

    /**
     * Returns array of files in provided directory
     *
     * @param string $path Local path in storage
     *
     * @return string[]
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    abstract protected function doGetFiles(string $path): array;

    /**
     * Creates the file or updates its content
     *
     * @param string $path    Local path to file in current storage
     * @param string $content String content of the file
     *
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    abstract protected function doPutFile(string $path, string $content): void;

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    abstract protected function doDeleteFile(string $path): bool;

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    abstract protected function doDeleteDirectory(string $path): bool;
}
