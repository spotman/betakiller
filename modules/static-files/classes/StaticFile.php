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

    /**
     * Поиск по проекту статичного файла
     * (полный путь к файлу)
     *
     * @param string $file
     *
     * @return string
     */
    public static function findOriginal(string $file): string
    {
        $info = pathinfo($file);
        $dir  = ($info['dirname'] !== '.') ? $info['dirname'].'/' : '';

        return Kohana::find_file('static-files', $dir.$info['filename'], $info['extension']);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getFullUrl(string $path): string
    {
        return $this->config('url').$path;
    }

    protected function config($key)
    {
        return $this->_config->$key;
    }
}
