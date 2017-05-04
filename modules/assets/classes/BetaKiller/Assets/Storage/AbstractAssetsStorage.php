<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\AssetsStorageException;
use BetaKiller\Assets\Model\AssetsModelInterface;

/**
 * Class AbstractAssetsStorage
 * Abstract storage for assets
 */
abstract class AbstractAssetsStorage
{
    /**
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function get(AssetsModelInterface $model)
    {
        $file_path = $model->getStorageFileName();

        return $this->doGet($file_path);
    }

    /**
     * Stores file
     *
     * @param AssetsModelInterface $model
     * @param string               $content
     */
    public function put(AssetsModelInterface $model, $content)
    {
        $file_path = $model->getStorageFileName();

        $this->doPut($file_path, $content);
    }

    /**
     * Deletes the file
     *
     * @param AssetsModelInterface $model
     */
    public function delete(AssetsModelInterface $model)
    {
        $file_path = $model->getStorageFileName();

        $this->doDelete($file_path);
    }

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     *
     * @return string
     * @throws AssetsStorageException
     */
    abstract protected function doGet($path);

    /**
     * Creates the file or updates its content
     *
     * @param string $path    Local path to file in current storage
     * @param string $content String content of the file
     *
     * @throws AssetsStorageException
     */
    abstract protected function doPut($path, $content);

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return bool
     * @throws AssetsStorageException
     */
    abstract protected function doDelete($path);
}
