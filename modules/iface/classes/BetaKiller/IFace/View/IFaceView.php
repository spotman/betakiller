<?php
namespace BetaKiller\IFace\View;

use BetaKiller\IFace\IFaceInterface;
use Link;
use Meta;
use View;

class IFaceView
{
    /**
     * @var string
     */
    protected $layout;

    /**
     * @var string
     */
    protected $wrapperCodename = \BetaKiller\IFace\View\WrapperView::HTML5;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Helper for changing wrapper from view
     *
     * @param string $wrapper
     */
    public function setWrapperCodename($wrapper)
    {
        $this->wrapperCodename = $wrapper;
    }

    public function render(IFaceInterface $iface)
    {
        $view_path  = $this->getViewPath($iface);
        $iface_view = $this->view_factory($view_path);

        // Getting IFace data
        $this->data = $iface->getData();

        // For changing wrapper from view via $_this->wrapper('html')
        $this->data['iface'] = [
            'label'    => $iface->getLabel(),
            'codename' => $iface->getCodename(),
        ];

        $iface_view->set($this->data);

        $meta = Meta::instance();

        // Setting page title
        $meta->title($iface->getTitle());

        // Setting page description
        $meta->description($iface->getDescription());

        Link::instance()
            ->canonical($iface->url(null, false));

        // TODO move calls for Meta and Link to overrided methods in Wrapper

        // Getting IFace layout
        $this->layout = $iface->getLayoutCodename();

        $layout = $this->processLayout($iface_view);

        return $this->processWrapper($layout);
    }

    protected function processLayout(View $iface_view)
    {
        return LayoutView::factory($this->layout)
            ->setContent($iface_view)
            ->render();
    }

    protected function processWrapper($layout)
    {
        return $this->wrapperViewFactory($this->wrapperCodename)
            ->setContent($layout)
            ->render();
    }

    protected function layoutViewFactory($path)
    {
        return LayoutView::factory($path);
    }

    protected function wrapperViewFactory($path)
    {
        return WrapperView::factory($path);
    }

    /**
     * @param $path
     *
     * @return View
     */
    protected function view_factory($path)
    {
        return View::factory($path);
    }

    protected function getViewPath(IFaceInterface $iface)
    {
        return 'ifaces'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $iface->getCodename());
    }
}
