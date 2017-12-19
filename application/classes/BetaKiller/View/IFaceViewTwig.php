<?php
namespace BetaKiller\View;

use BetaKiller\IFace\View\IFaceView;
use Twig;

class IFaceViewTwig extends IFaceView
{
    /**
     * @param $path
     *
     * @return Twig
     */
    protected function viewFactory(string $path): ViewInterface
    {
        // Use Twig templates instead of Kohana views
        return Twig::factory($path);
    }

    /**
     * @param \BetaKiller\View\ViewInterface $ifaceView
     *
     * @return string
     */
    protected function processLayout(ViewInterface $ifaceView): string
    {
        $layoutPath = $this->layoutViewFactory($this->layout)->getViewPath();

        // Extend layout inside of IFace view via "extend" tag
        return $ifaceView->set('layout', $layoutPath)->render();
    }

    protected function layoutViewFactory(string $path)
    {
        return LayoutViewTwig::factory($path);
    }

    protected function wrapperViewFactory(string $path)
    {
        return WrapperViewTwig::factory($path);
    }
}
