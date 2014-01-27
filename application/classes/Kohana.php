<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana extends Kohana_Core {

    public static $environment_string = 'development';

    public static function prepend_path($path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        array_unshift(static::$_paths, $path);
    }

    public static function modules(array $modules = NULL)
    {
        $result = parent::modules($modules);

        if ( $modules !== NULL )
        {
            MultiSite::instance()->init_site();
        }

        return $result;
    }

    public static function in_production()
    {
        return in_array(Kohana::$environment, array(Kohana::PRODUCTION, Kohana::STAGING));
    }

    public static function config($file)
    {
        return Kohana::$config->load($file);
    }

    public static function load_if_exists($file)
    {
        if ( ! file_exists($file) )
            return NULL;

        return parent::load($file);
    }

}