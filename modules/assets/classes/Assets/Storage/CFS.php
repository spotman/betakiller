<?php defined('SYSPATH') OR die('No direct script access.');


class Assets_Storage_CFS extends Assets_Storage_Local {

    /**
     * Creates the file or updates its content
     *
     * @param string $path Local path to file in current storage
     * @param string $content String content of the file
     * @throws Assets_Storage_Exception
     */
    protected function _put($path, $content)
    {
        throw new Assets_Storage_Exception('CFS storage does not support adding files');
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
        throw new Assets_Storage_Exception('CFS storage does not support deleting files');
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
//        $path_info = pathinfo($path);

        throw new Assets_Storage_Exception('Implement me!');

//        $found = Kohana::find_file($path)
        // TODO: Implement _get() method.
    }

}