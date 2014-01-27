<?php defined('SYSPATH') OR die('No direct script access.');

class File_Provider_Database extends File_Provider {

    public function by_model(File_Model_Database $model)
    {
        // Getting path from model (or default path)
        $path = $model->loaded()
            ? $model->get_path()
            : $this->get_default();

        // TODO
        $storage_codename = '';

        // Getting storage
        $storage = File_Storage::factory($storage_codename);

        // Getting File_Model from storage by path
    }


}