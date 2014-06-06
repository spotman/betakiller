<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana extends Kohana_Core {

    public static $environment_string = 'development';

    /**
     * @var Cache
     */
    protected static $_custom_cache;

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

    public static function doc_root()
    {
        static $path;

        if ( ! $path )
        {
            $path = ( php_sapi_name() == 'cli' )
                ? dirname(realpath($_SERVER['argv'][0]))
                : getenv('DOCUMENT_ROOT');
        }

        if ( ! $path )
            throw new Kohana_Exception('Can not detect document root');

        return $path;
    }

    public static function cache($name, $data = NULL, $lifetime = 60)
    {
        if ( ! static::$caching )
            return NULL;

        return static::$_custom_cache
            ? static::custom_cache($name, $data, $lifetime)
            : parent::cache($name, $data, $lifetime);
    }

    protected static function custom_cache($name, $data = NULL, $lifetime = 60)
    {
        $key = sha1($name);

        if ( is_null($data) )
        {
            $value = static::$_custom_cache->get($key);
            static::log_with_headers('Read-Cache-Key-'.$key, '['.gettype($value).']');
            return $value;
        }
        else
        {
            static::log_with_headers('Write-Cache-Key-'.$key, '['.gettype($data).'] for '. $lifetime);
            return static::$_custom_cache->set($key, $data, $lifetime);
        }
    }

    protected static function log_with_headers($key, $value)
    {
        $response = Response::current();

        if ( $response )
        {
            $response->headers($key, $value);
        }
    }

    public static function set_custom_cache(Cache $instance)
    {
        static::$_custom_cache = $instance;
    }

}