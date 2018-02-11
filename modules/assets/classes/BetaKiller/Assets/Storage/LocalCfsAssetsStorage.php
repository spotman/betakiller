<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\AssetsStorageException;

class LocalCfsAssetsStorage implements AssetsStorageInterface
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
     *  Returns true if files are located under document root
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        // Static assets are located outside of the docroot
        return false;
    }

    /**
     * Creates the file or updates its content
     *
     * @param string $path    Local path to file in current storage
     * @param string $content String content of the file
     *
     * @throws AssetsStorageException
     */
    public function putFile(string $path, string $content): void
    {
        throw new AssetsStorageException('CFS storage does not support adding files');
    }

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return void
     * @throws AssetsStorageException
     */
    public function deleteFile(string $path): void
    {
        throw new AssetsStorageException('CFS storage does not support deleting files');
    }

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     *
     * @return string
     * @throws AssetsStorageException
     */
    public function getFile(string $path): string
    {
        $extDotPosition = mb_strrpos($path, '.');

        if ($extDotPosition === false) {
            throw new AssetsStorageException('Path must point to file with extension, :value given instead', [
                ':value' => $path,
            ]);
        }

        $path = mb_substr($path, 0, $extDotPosition);
        $ext = mb_substr($path, $extDotPosition+1);

        return \Kohana::find_file($this->basePath, $path, $ext);
    }

    /**
     * Returns array of files in provided directory
     *
     * @param string $path Local path in storage
     *
     * @return string[]
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function getFiles(string $path): array
    {
        throw new AssetsStorageException('Implement me!');
    }

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return void
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function deleteDirectory(string $path): void
    {
        throw new AssetsStorageException('CFS storage does not support deleting files');
    }
}
