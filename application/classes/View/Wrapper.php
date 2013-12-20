<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class View_Wrapper
 *
 * View wrapper
 *
 * @package BetaKiller
 * @author Spotman
 */
class View_Wrapper extends Kohana_View_Wrapper {

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

}
