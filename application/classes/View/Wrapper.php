<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class View_Wrapper
 *
 * View wrapper
 *
 * @package BetaKiller
 * @author Spotman
 */
class View_Wrapper extends Core_View_Wrapper
{
    protected function view_factory($view_path)
    {
        // Using Twig instead of View
        return Twig::factory($view_path);
    }

    protected function get_view_path($codename)
    {
        // Using aliases in Twig
        return '@'.parent::get_view_path($codename);
    }

    // TODO move to BetaKiller_Twig_Extension
    protected function get_data()
    {
        return array(
            'meta_tags' =>  Meta::instance()->render(),
            'js_all'    =>  JS::instance()->get_all(),
            'css_all'   =>  CSS::instance()->get_all(),
            'links_all' =>  Link::instance()->render(),
        );
    }
}
