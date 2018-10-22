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

    public function render(ViewInterface $ifaceView, HtmlRenderHelper $renderHelper): string
    {
        $layoutPath = $this->getLayoutViewPath($renderHelper->getLayoutCodename());
        $layoutView = $this->createView($layoutPath);

        // Inject helper objects
        foreach ($renderHelper->getLayoutHelperObjects() as $key => $value) {
            $ifaceView->set($key, $value);
        }

        $layoutView->set('content', $ifaceView->render());

        return $this->wrap($layoutView->render(), $renderHelper);
    }

    protected function wrap(string $layoutContent, HtmlRenderHelper $helper): string
    {
        $wrapperPath = $this->getWrapperViewPath($helper->getWrapperCodename());
        $view        = $this->createView($wrapperPath);

        foreach ($helper->getWrapperData() as $key => $value) {
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
