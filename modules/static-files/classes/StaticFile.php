<?php defined('SYSPATH') or die('No direct access allowed.');

class StaticFile extends Kohana_StaticFile {

    use Util_Singleton;

    public function __construct()
    {
        parent::__construct();
    }

    public function getLink($path)
    {
        return $this->config('url') . $path;
    }

    protected function config($key)
    {
        return $this->_config->$key;
    }

}