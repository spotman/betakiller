<?php
namespace BetaKiller\View;

class TwigLayoutView extends LayoutView
{
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
     * @param \BetaKiller\View\ViewInterface    $ifaceView
     * @param \BetaKiller\View\HtmlRenderHelper $renderHelper
     *
     * @return string
     */
    public function render(ViewInterface $ifaceView, HtmlRenderHelper $renderHelper): string
    {
        $layoutPath = $this->getLayoutViewPath($renderHelper->getLayoutCodename());

        // Inject objects for Twig helper functions
        foreach ($renderHelper->getLayoutHelperObjects() as $key => $value) {
            $ifaceView->set($key, $value);
        }

        // Extend layout inside IFace view via "extend" tag
        $content = $ifaceView->set('layout', $layoutPath)->render();

        return $this->wrap($content, $renderHelper);
    }
}
