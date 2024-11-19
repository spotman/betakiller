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
     * @param \BetaKiller\View\ViewInterface   $ifaceView
     * @param \BetaKiller\View\TemplateContext $context
     *
     * @return string
     */
    public function render(ViewInterface $ifaceView, TemplateContext $context): string
    {
        // Inject objects for Twig helper functions
        foreach ($context->getTemplateData() as $key => $value) {
            $ifaceView->set($key, $value);
        }

        if ($context->hasLayout()) {
            $layoutPath = $this->getLayoutViewPath($context->getLayout());

            // Extend layout inside IFace view via "extend" tag
            $ifaceView->set('layout', $layoutPath);
        }

        return $this->wrap($ifaceView->render(), $context);
    }
}
