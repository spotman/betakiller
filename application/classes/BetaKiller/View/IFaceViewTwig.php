<?php
namespace BetaKiller\View;

use BetaKiller\IFace\View\IFaceView;
use Twig;
use View;

class IFaceViewTwig extends IFaceView
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
     * @param View $ifaceView
     *
     * @return View
     */
    protected function processLayout(View $ifaceView)
    {
        $layoutPath = $this->layoutViewFactory($this->layout)->getViewPath();

        // Extend layout inside of IFace view via "extend" tag
        return $ifaceView->set('layout', $layoutPath);
    }

    protected function layoutViewFactory($path)
    {
        return LayoutViewTwig::factory($path);
    }

    protected function wrapperViewFactory($path)
    {
        return WrapperViewTwig::factory($path);
    }
}
