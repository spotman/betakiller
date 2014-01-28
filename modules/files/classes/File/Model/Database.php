<?php defined('SYSPATH') OR die('No direct script access.');

// TODO fillup from Model_File
interface File_Model_Database {

    /**
     * Checks if model is found
     *
     * @return bool
     */
    public function loaded();

    /**
     * Returns path for file in storage
     *
     * @return string
     */
    public function get_filename();
}