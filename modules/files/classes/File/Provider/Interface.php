<?php defined('SYSPATH') OR die('No direct script access.');

interface File_Provider_Interface {

    /**
     * @param File_Model_Interface $file
     * @return File
     */
    public function get(File_Model_Interface $file);

    public function put(File_Model_Interface $file);

}