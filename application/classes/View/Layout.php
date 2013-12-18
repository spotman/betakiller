<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class View_Layout
 *
 * Layout wrapper with set_title() / get_title() helpers
 *
 * @package BetaKiller
 * @author Spotman
 */
class View_Layout extends Twig {

    /**
     * @param null $file
     * @param array $data
     * @return static
     */
    public static function factory($file = NULL, array $data = NULL)
    {
        return parent::factory('@layouts/'.$file, $data);
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
