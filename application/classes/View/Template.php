<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class View_Template
 *
 * Template wrapper with set_title() / get_title() helpers
 */
class View_Template extends Twig {

    public static function factory($file = NULL, array $data = NULL)
    {
        return parent::factory('@templates/'.$file, $data);
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
