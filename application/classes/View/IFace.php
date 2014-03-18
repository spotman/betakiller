<?php defined('SYSPATH') OR die('No direct script access.');

class View_IFace extends Core_View_IFace {

    /**
     * @param $path
     * @return Twig
     */
    protected function view_factory($path)
    {
        // Use Twig templates instead of Kohana views
        return Twig::factory($path);
    }

    /**
     * @param View $iface_view
     * @return Twig
     */
    protected function process_layout(View $iface_view)
    {
        $layout_path = View_Layout::factory($this->_layout)->get_view_path();

        // Extend layout inside of IFace view via "extend" tag
        return $iface_view->set('layout', $layout_path);
    }

}
