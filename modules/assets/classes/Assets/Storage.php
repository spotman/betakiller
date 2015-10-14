<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Assets_Storage
 * Abstract storage for assets
 */
abstract class Assets_Storage {

    /**
     * @param Assets_Model $model
     * @return string
     */
    public function get(Assets_Model $model)
    {
        $file_path = $model->get_storage_file_name();

        return $this->_get($file_path);
    }

    /**
     * Stores file
     *
     * @param Assets_Model $model
     * @param string $content
     */
    public function put(Assets_Model $model, $content)
    {
        $file_path = $model->get_storage_file_name();

        $this->_put($file_path, $content);
    }

    /**
     * Deletes the file
     *
     * @param Assets_Model $model
     */
    public function delete(Assets_Model $model)
    {
        $file_path = $model->get_storage_file_name();

        $this->_delete($file_path);
    }

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     * @return string
     * @throws Assets_Storage_Exception
     */
    abstract protected function _get($path);

    /**
     * Creates the file or updates its content
     *
     * @param string $path Local path to file in current storage
     * @param string $content String content of the file
     * @throws Assets_Storage_Exception
     */
    abstract protected function _put($path, $content);

    /**
     * Deletes file
     *
     * @param string $path Local path
     * @return bool
     * @throws Assets_Storage_Exception
     */
    abstract protected function _delete($path);

}
