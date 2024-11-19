<?php
namespace BetaKiller\View;

interface LayoutViewInterface
{
    /**
     * @param \BetaKiller\View\ViewInterface   $ifaceView
     * @param \BetaKiller\View\TemplateContext $context
     *
     * @return string
     */
    public function render(ViewInterface $ifaceView, TemplateContext $context): string;
}
