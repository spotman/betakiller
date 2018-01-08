<?php
namespace BetaKiller\View;

use BetaKiller\IFace\View\LayoutView;
use Twig;

class LayoutViewTwig extends LayoutView
{
    /**
     * Using Twig layouts now
     *
     * @param $path
     *
     * @return Twig
     */
    protected function viewFactory(string $path): ViewInterface
    {
        return Twig::factory($path);
    }

    public function getViewPath(): string
    {
        // Using Twig namespaces
        return '@'.parent::getViewPath();
    }
}
