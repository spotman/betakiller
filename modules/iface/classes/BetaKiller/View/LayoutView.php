<?php

namespace BetaKiller\View;

class LayoutView implements LayoutViewInterface
{
    /**
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * LayoutView constructor.
     *
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     */
    public function __construct(ViewFactoryInterface $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function render(ViewInterface $ifaceView, TemplateContext $context): string
    {
        // Inject helper objects
        foreach ($context->getTemplateData() as $key => $value) {
            $ifaceView->set($key, $value);
        }

        $content = $ifaceView->render();

        if ($context->hasLayout()) {
            $layoutPath = $this->getLayoutViewPath($context->getLayout());
            $layoutView = $this->createView($layoutPath);

            $content = $layoutView->set('content', $content)->render();
        }

        return $this->wrap($content, $context);
    }

    protected function wrap(string $layoutContent, TemplateContext $context): string
    {
        if (!$context->hasWrapper()) {
            return $layoutContent;
        }

        $wrapperPath = $this->getWrapperViewPath($context->getWrapper());
        $view        = $this->createView($wrapperPath);

        foreach ($context->getWrapperData() as $key => $value) {
            $view->set($key, $value);
        }

        $view->set('content', $layoutContent);

        return $view->render();
    }

    protected function getLayoutViewPath(string $layoutCodename): string
    {
        return $this->getLayoutBasePath().DIRECTORY_SEPARATOR.$layoutCodename;
    }

    protected function getWrapperViewPath(string $wrapperCodename): string
    {
        return $this->getWrapperBasePath().DIRECTORY_SEPARATOR.$wrapperCodename;
    }

    protected function getLayoutBasePath(): string
    {
        return 'layouts';
    }

    protected function getWrapperBasePath(): string
    {
        return 'wrappers';
    }

    /**
     * @param string $path
     *
     * @return \BetaKiller\View\ViewInterface
     */
    protected function createView(string $path): ViewInterface
    {
        return $this->viewFactory->create($path);
    }
}
