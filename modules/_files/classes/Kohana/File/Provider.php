<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_File_Provider {

    /**
     * @var string
     */
    protected $_codename;

    public static function factory($codename)
    {
        $class_name = 'File_Provider_'.$codename;

        if ( ! file_exists($class_name) )
        {
            throw new File_Provider_Exception('Unknown provider :class_name', array(':class_name' => $class_name));
        }

        $instance = new $class_name($codename);


        return $instance;
    }

    public function __construct($codename)
    {
        $this->_codename = $codename;

        // Load config for current provider
    }

//    public function config($)
//    {
//
//    }


    public function get_default()
    {
        $value = $this->config('default');

        if ( ! $value )
            throw new File_Provider_Exception('Empty "default" config value for :provider',
                array(':provider' => $this->_codename)
            );

        return $value;
    }

    protected function config($key = NULL, $default_value = NULL)
    {
        static $config;

        if ( ! $config )
        {
            $config = Kohana::$config->load('files')->as_array();
        }

        return $key
            ? $config
            : Arr::path($config, $key, $default_value);
    }

    public function get_path()
    {
        return '';
    }

    public function storage_factory($storage_codename)
    {
        return File_Storage::factory($storage_codename);
    }

    public function get_public_url(File_Model $model)
    {
        // Надо ли?
    }

//    /**
//     * @param File_Model_Interface $file
//     * @return File
//     */
//    public function get(File_Model_Interface $file);
//
//    public function put(File_Model_Interface $file);

}
