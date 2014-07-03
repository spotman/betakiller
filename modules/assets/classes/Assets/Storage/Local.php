<?php defined('SYSPATH') OR die('No direct script access.');


class Assets_Storage_Local extends Assets_Storage {

    protected $_base_path;

    // TODO move mask to config
    protected $_dir_mask = 0775;

    // TODO move mask to config
    protected $_file_mask = 0664;

    /**
     * @param string $base_path
     * @return $this
     * @throws Assets_Storage_Exception
     */
    public function set_base_path($base_path)
    {
        if ( ! file_exists($base_path) OR ! is_dir($base_path) )
            throw new Assets_Storage_Exception('Incorrect path provided');

        $this->_base_path = $base_path;
        return $this;
    }

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     * @return string
     * @throws Assets_Storage_Exception
     */
    protected function _get($path)
    {
        return file_get_contents( $this->_make_full_path($path) );
    }

    /**
     * Creates the file or updates its content
     *
     * @param string $path Local path to file in current storage
     * @param string $content String content of the file
     * @throws Assets_Storage_Exception
     */
    protected function _put($path, $content)
    {
        $full_path = $this->_make_full_path($path);

        $base_path = dirname($full_path);

        if ( ! file_exists($base_path) )
        {
            try
            {
                mkdir($base_path, $this->_dir_mask, TRUE);
            }
            catch ( Exception $e)
            {
                throw new Assets_Storage_Exception('Can not create path :dir', array(':dir' => $base_path));
            }
        }

        file_put_contents($full_path, $content);

        chmod($full_path, $this->_file_mask);
    }

    /**
     * Deletes file
     *
     * @param string $path Local path
     * @return bool
     * @throws Assets_Storage_Exception
     */
    protected function _delete($path)
    {
        unlink( $this->_make_full_path($path) );
    }

    protected function _make_full_path($relative_path)
    {
        return $this->_base_path . DIRECTORY_SEPARATOR . $relative_path;
    }

}