<?php defined('SYSPATH') or die('No direct access allowed.');

class StaticFile extends Kohana_StaticFile
{
    private static $instance;

    /**
     * @return static
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function getLink($path)
    {
        return $this->config('url') . $path;
    }

    protected function config($key)
    {
        return $this->_config->$key;
    }

    public function get_cache_folders()
    {
        $folders = array();

        $cache_paths = array($this->config('cache'), $this->config('url'));
        $base_path = rtrim($this->config('path'), DIRECTORY_SEPARATOR);

        foreach ( $cache_paths as $path )
        {
            $folders[] = rtrim($base_path . $path, DIRECTORY_SEPARATOR);
        }

        return $folders;
    }

}
