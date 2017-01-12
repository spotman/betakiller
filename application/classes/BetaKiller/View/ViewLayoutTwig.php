<?php
namespace BetaKiller\View;

use Twig;

class ViewLayoutTwig extends \View_Layout
{
    /**
     * Using Twig layouts now
     *
     * @param $path
     *
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
