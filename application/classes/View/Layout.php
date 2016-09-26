<?php defined('SYSPATH') OR die('No direct script access.');

class View_Layout extends Core_View_Layout {

    /**
     * Using Twig layouts now
     *
     * @param $path
     * @return Twig
     */
    protected function view_factory($path)
    {
        return Twig::factory($path);
    }

    public function get_view_path()
    {
        // Using Twig namespaces
        return '@'.parent::get_view_path();
    }

}
