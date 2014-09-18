<?php defined('SYSPATH') OR die('No direct script access.');

class File_Provider_Database extends File_Provider {

    /**
     * @param File_Model_Database $model
     * @return File_Model
     */
    public function by_model(File_Model_Database $model)
    {
        // Getting filename from model (or default filename)
        $filename = $model->loaded()
            ? $model->get_filename()
            : $this->get_default();

        // Getting base path for current provider
        $base_path = $this->get_path();

        // Making full path of requested file
        $full_path = $base_path.DIRECTORY_SEPARATOR.$filename;

        $storage_codename = '';

        // Getting storage
        $storage = $this->storage_factory($storage_codename);

        // Getting File_Model from storage by full path
        $storage->get();
    }


}
