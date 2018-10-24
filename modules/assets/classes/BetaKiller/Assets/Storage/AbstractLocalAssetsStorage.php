<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\Exception\AssetsStorageException;

abstract class AbstractLocalAssetsStorage extends AbstractAssetsStorage
{
    /**
     * Allow creating nested files and directories (groups/other security must be done via server umask config)
     *
     * @var int
     */
    private $dirMode = 0777;

    /**
     * Prevent executing (groups/other security must be done via server umask config)
     *
     * @var int
     */
    private $fileMode = 0666;

    /**
     * @param string $basePath
     *
     * @throws AssetsStorageException
     */
    public function setBasePath(string $basePath): void
    {
        $realPath = realpath($basePath);

        if (!$realPath || !file_exists($realPath) || !is_dir($realPath)) {
            throw new AssetsStorageException('Incorrect path provided :value', [':value' => $basePath]);
        }

        parent::setBasePath($realPath);
    }

    /**
     * @return string
     */
    protected function getDirectorySeparator(): string
    {
        return DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $path
     *
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    private function checkFileExists(string $path): void
    {
        if (!file_exists($path)) {
            throw new AssetsStorageException('File :path does not exists', [':path' => $path]);
        }
    }

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     *
     * @return string
     * @throws AssetsStorageException
     */
    protected function doGetFile(string $path): string
    {
        $this->checkFileExists($path);

        return file_get_contents($path);
    }

    /**
     * Creates the file or updates its content
     *
     * @param string $path    Local path to file in current storage
     * @param string $content String content of the file
     *
     * @throws AssetsStorageException
     */
    protected function doPutFile(string $path, string $content): void
    {
        $baseDir = \dirname($path);

        if (!$this->createDirectory($baseDir)) {
            throw new AssetsStorageException('Can not create path :dir', [':dir' => $baseDir]);
        }

        file_put_contents($path, $content);

        chmod($path, $this->fileMode);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function createDirectory(string $path): bool
    {
        if (file_exists($path) && is_dir($path)) {
            return true;
        }

        return mkdir($path, $this->dirMode, true) || is_dir($path);
    }

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return bool
     * @throws AssetsStorageException
     */
    protected function doDeleteFile(string $path): bool
    {
        $this->checkFileExists($path);

        return unlink($path);
    }

    /**
     * Returns array of files in provided directory
     *
     * @param string $directory
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    protected function doGetFiles(string $directory): array
    {
        $this->checkFileExists($directory);

        $files = [];

        foreach (glob($directory.DIRECTORY_SEPARATOR.'*') as $file) {
            $files[] = $file;
        }

        return $files;
    }

    /**
     * Delete provided directory. Throws an exception if there are files inside
     *
     * @param string $path
     *
     * @return bool
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    protected function doDeleteDirectory(string $path): bool
    {
        $this->checkFileExists($path);

        try {
            return rmdir($path);
        } catch (\Throwable $e) {
            throw AssetsStorageException::wrap($e);
        }
    }
}
