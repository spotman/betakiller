<?php
namespace BetaKiller\View;

interface LayoutViewInterface
{
    /**
     * @param \BetaKiller\View\ViewInterface    $ifaceView
     * @param \BetaKiller\View\HtmlRenderHelper $renderHelper
     *
     * @return string
     */
    public function render(ViewInterface $ifaceView, HtmlRenderHelper $renderHelper): string;
}
