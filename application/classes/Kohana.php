<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana extends Kohana_Core {

    public static $environment_string = 'development';

    /**
     * @var Cache
     */
    protected static $_custom_cache;

    public static function in_production($use_staging = FALSE)
    {
        $values = $use_staging
            ? array(Kohana::PRODUCTION, Kohana::STAGING)
            : array(Kohana::PRODUCTION);

        return in_array(Kohana::$environment, $values, true);
    }

    public static function in_staging()
    {
        return (Kohana::$environment === Kohana::STAGING);
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

    public static function getPaths()
    {
        return static::$_paths;
    }

    public static function modules(array $modules = NULL)
    {
        $result = parent::modules($modules);

        if ( $modules !== NULL )
        {
            MultiSite::instance()->initSite();
        }

        return $result;
    }

    public static function reinit()
    {
        self::$_init = FALSE;

        // Drop cache because of init file
        self::$config->drop_cache();

        $config = self::config('init')->as_array();
        parent::init($config);
    }

    public static function cache($name, $data = NULL, $lifetime = 60)
    {
        if ( ! static::$caching ) {
            return NULL;
        }

        return static::$_custom_cache
            ? static::custom_cache($name, $data, $lifetime)
            : parent::cache($name, $data, $lifetime);
    }

    protected static function custom_cache($name, $data = NULL, $lifetime = 60)
    {
        $key = sha1($name);

        if (null === $data) {
            return static::$_custom_cache->get($key);
        }

        return static::$_custom_cache->set($key, $data, $lifetime);
    }

    public static function set_custom_cache(Cache $instance)
    {
        static::$_custom_cache = $instance;
    }
}
