<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class View_Wrapper
 *
 * Request wrapper with set_content() / get_content() helpers
 */
class View_Wrapper extends View {

    public function set_filename($file)
    {
        $path = static::get_view_path() . DIRECTORY_SEPARATOR . $file;
        return parent::set_filename($path);
    }

    protected static function get_view_path()
    {
        return 'wrappers';
    }

    public function __construct($file = NULL, array $data = NULL)
    {
        parent::__construct($file, $data);
        $this->set_title()->set_content();
    }

    public function set_content($string = '')
    {
        $this->set('content', $string);
        return $this;
    }

    public function get_content()
    {
        return $this->get('content');
    }

    public function set_title($string = '')
    {
        $this->set('title', $string);
        return $this;
    }

    public function get_title()
    {
        return $this->get('title');
    }

}
