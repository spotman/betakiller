<?php

namespace BetaKiller\Assets;

use Assets_Storage_Exception;

/**
 * Class AssetsStorage
 * Abstract storage for assets
 */
abstract class AssetsStorage
{

    /**
     * @param AssetsModelInterface $model
     *
     * @return string
     */
    public function get(AssetsModelInterface $model)
    {
        $file_path = $model->getStorageFileName();

        return $this->_get($file_path);
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

        $this->_put($file_path, $content);
    }

    /**
     * Deletes the file
     *
     * @param AssetsModelInterface $model
     */
    public function delete(AssetsModelInterface $model)
    {
        $file_path = $model->getStorageFileName();

        $this->_delete($file_path);
    }

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     *
     * @return string
     * @throws Assets_Storage_Exception
     */
    abstract protected function _get($path);

    /**
     * Creates the file or updates its content
     *
     * @param string $path    Local path to file in current storage
     * @param string $content String content of the file
     *
     * @throws Assets_Storage_Exception
     */
    abstract protected function _put($path, $content);

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return bool
     * @throws Assets_Storage_Exception
     */
    abstract protected function _delete($path);

}
