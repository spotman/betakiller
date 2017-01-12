<?php
namespace BetaKiller\View;

use Twig;
use View;
use View_IFace;

class ViewIFaceTwig extends View_IFace
{
    /**
     * @param $path
     *
     * @return Twig
     */
    protected function view_factory($path)
    {
        // Use Twig templates instead of Kohana views
        return Twig::factory($path);
    }

    /**
     * @param View $iface_view
     *
     * @return View
     */
    protected function process_layout(View $iface_view)
    {
        $layout_path = $this->layout_view_factory($this->_layout)->get_view_path();

        // Extend layout inside of IFace view via "extend" tag
        return $iface_view->set('layout', $layout_path);
    }

    protected function layout_view_factory($path)
    {
        return ViewLayoutTwig::factory($path);
    }

    protected function wrapper_view_factory($path)
    {
        return ViewWrapperTwig::factory($path);
    }
}
