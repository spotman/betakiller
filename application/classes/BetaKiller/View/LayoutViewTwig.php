<?php
namespace BetaKiller\View;

use Twig;

class LayoutViewTwig extends \BetaKiller\IFace\View\LayoutView
{
    /**
     * Using Twig layouts now
     *
     * @param $path
     *
     * @return Twig
     */
    protected function viewFactory($path)
    {
        return Twig::factory($path);
    }

    public function getViewPath()
    {
        // Using Twig namespaces
        return '@'.parent::getViewPath();
    }
}
