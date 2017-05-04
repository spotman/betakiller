<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\AssetsStorageException;
use Exception;


class AssetsStorageLocal extends AbstractAssetsStorage
{
    private $basePath;

    // TODO move mask to config
    private $dirMask = 0775;

    // TODO move mask to config
    private $fileMask = 0664;

    /**
     * @param string $basePath
     *
     * @return $this
     * @throws AssetsStorageException
     */
    public function setBasePath($basePath)
    {
        if (!file_exists($basePath) || !is_dir($basePath)) {
            throw new AssetsStorageException('Incorrect path provided :value', [':value' => $basePath]);
        }

        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     *
     * @return string
     * @throws AssetsStorageException
     */
    protected function doGet($path)
    {
        return file_get_contents($this->makeFullPath($path));
    }

    /**
     * Creates the file or updates its content
     *
     * @param string $path    Local path to file in current storage
     * @param string $content String content of the file
     *
     * @throws AssetsStorageException
     */
    protected function doPut($path, $content)
    {
        $full_path = $this->makeFullPath($path);

        $base_path = dirname($full_path);

        if (!file_exists($base_path)) {
            try {
                mkdir($base_path, $this->dirMask, true);
            } catch (Exception $e) {
                throw new AssetsStorageException('Can not create path :dir', [':dir' => $base_path]);
            }
        }

        file_put_contents($full_path, $content);

        chmod($full_path, $this->fileMask);
    }

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return bool
     * @throws AssetsStorageException
     */
    protected function doDelete($path)
    {
        return unlink($this->makeFullPath($path));
    }

    private function makeFullPath($relative_path)
    {
        return $this->basePath.DIRECTORY_SEPARATOR.$relative_path;
    }
}
