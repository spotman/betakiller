<?php
namespace BetaKiller\View;

use CSS;
use JS;

class LayoutViewTwig extends LayoutView
{
    /**
     * @var \JS
     */
    private $js;

    /**
     * @var \CSS
     */
    private $css;

    /**
     * LayoutViewTwig constructor.
     *
     * @param \BetaKiller\View\ViewFactoryInterface $factory
     * @param \JS                                   $js
     * @param \CSS                                  $css
     * @param \BetaKiller\View\HtmlHeadHelper       $headHelper
     */
    public function __construct(ViewFactoryInterface $factory, JS $js, CSS $css, HtmlHeadHelper $headHelper)
    {
        $this->js  = $js;
        $this->css = $css;

        parent::__construct($factory, $headHelper);
    }

    protected function getLayoutBasePath(): string
    {
        // Using Twig namespaces
        return '@layouts';
    }

    protected function getWrapperBasePath(): string
    {
        // Using Twig namespaces
        return '@wrappers';
    }

    /**
     * @param \BetaKiller\View\ViewInterface $ifaceView
     *
     * @return string
     */
    public function render(ViewInterface $ifaceView): string
    {
        $layoutPath = $this->getLayoutViewPath();

        // Extend layout inside of IFace view via "extend" tag
        return $this->wrap(
            $ifaceView->set('layout', $layoutPath)->render()
        );
    }

    protected function getWrapperData(): array
    {
        return parent::getWrapperData() + [
                'js_all'  => $this->js->getAll(),
                'css_all' => $this->css->getAll(),
            ];
    }
}
