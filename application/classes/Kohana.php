<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana extends Kohana_Core {

    public static $environment_string = 'development';

    public static function prepend_path($path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        array_unshift(static::$_paths, $path);
    }

    public static function in_production()
    {
        return in_array(Kohana::$environment, array(Kohana::PRODUCTION, Kohana::STAGING));
    }

    public static function config($key)
    {
        return Kohana::$config->load($key);
    }

    public static function load_if_exists($file)
    {
        if ( ! file_exists($file) )
            return NULL;

        return parent::load($file);
    }

}