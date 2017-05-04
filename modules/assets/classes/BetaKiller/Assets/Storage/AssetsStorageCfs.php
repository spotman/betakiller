<?php namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\AssetsStorageException;

class AssetsStorageCfs extends AssetsStorageLocal
{
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
        throw new AssetsStorageException('CFS storage does not support adding files');
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
    protected function doGet($path)
    {
//        $path_info = pathinfo($path);

        throw new AssetsStorageException('Implement me!');

//        $found = Kohana::find_file($path)
    }
}
