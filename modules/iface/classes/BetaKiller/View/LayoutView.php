<?php
namespace BetaKiller\View;

class LayoutView implements LayoutViewInterface
{
    /**
     * @var string
     */
    private $layoutCodename;

    /**
     * @var string
     */
    private $wrapperCodename = self::WRAPPER_HTML5;

    /**
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var \BetaKiller\View\HtmlHeadHelper
     */
    private $headHelper;

    /**
     * LayoutView constructor.
     *
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     * @param \BetaKiller\View\HtmlHeadHelper       $headHelper
     */
    public function __construct(ViewFactoryInterface $viewFactory, HtmlHeadHelper $headHelper)
    {
        $this->viewFactory = $viewFactory;
        $this->headHelper = $headHelper;
    }

    /**
     * @return string
     */
    public function getLayoutCodename(): string
    {
        return $this->layoutCodename;
    }

    /**
     * @param string $layoutCodename
     *
     * @return \BetaKiller\View\LayoutViewInterface
     */
    public function setLayoutCodename(string $layoutCodename): LayoutViewInterface
    {
        $this->layoutCodename = $layoutCodename;

        return $this;
    }

    /**
     * @return string
     */
    public function getWrapperCodename(): string
    {
        return $this->wrapperCodename;
    }

    /**
     * @param string $wrapperCodename
     *
     * @return \BetaKiller\View\LayoutViewInterface
     */
    public function setWrapperCodename(string $wrapperCodename): LayoutViewInterface
    {
        $this->wrapperCodename = $wrapperCodename;

        return $this;
    }

    public function render(ViewInterface $ifaceView): string
    {
        $layoutPath = $this->getLayoutViewPath();
        $layoutView = $this->viewFactory->create($layoutPath);

        $layoutView->set('content', $ifaceView->render());

        return $this->wrap(
            $layoutView->render()
        );
    }

    /**
     * @return \BetaKiller\View\LayoutViewInterface
     */
    public function clear(): LayoutViewInterface
    {
        $this->headHelper->clear();

        return $this;
    }

    protected function wrap(string $layoutContent): string
    {
        $wrapperPath = $this->getWrapperViewPath();
        $view = $this->viewFactory->create($wrapperPath);

        foreach ($this->getWrapperData() as $key => $value) {
            $view->set($key, $value);
        }

        $view->set('content', $layoutContent);

        return $view->render();
    }

    protected function getWrapperData(): array
    {
        return [
            'head' => $this->headHelper->renderAll(),
        ];
    }

    protected function getLayoutViewPath(): string
    {
        return $this->getLayoutBasePath().DIRECTORY_SEPARATOR.$this->layoutCodename;
    }

    protected function getWrapperViewPath(): string
    {
        return $this->getWrapperBasePath().DIRECTORY_SEPARATOR.$this->wrapperCodename;
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
