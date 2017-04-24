<?php
namespace BetaKiller\Helper;

use BetaKiller\DI\ContainerTrait;
use BetaKiller\IFace\IFaceFactory;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\Widget\BaseWidget;

trait IFaceTrait
{
    use ContainerTrait;

    /**
     * @return \BetaKiller\IFace\Url\UrlDispatcher
     * @deprecated Use DI instead
     */
    protected function url_dispatcher()
    {
        return $this->getContainer()->get(\BetaKiller\IFace\Url\UrlDispatcher::class);
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlParameters
     * @deprecated Use DI instead
     */
    protected function url_parameters()
    {
        return $this->url_dispatcher()->parameters();
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     * @deprecated Use UrlParametersFactory::create() instead
     */
    protected function url_parameters_instance()
    {
        // Always new object
        return $this->getContainer()->make(\BetaKiller\IFace\Url\UrlParametersInterface::class);
    }

    /**
     * @param $codename
     * @return \BetaKiller\IFace\IFaceInterface
     * @deprecated Use DI for injecting IFaceFactory instead
     */
    protected function iface_from_codename($codename)
    {
        return IFaceFactory::instance()->from_codename($codename);
    }

    /**
     * @param $model \BetaKiller\IFace\IFaceModelInterface
     * @return \BetaKiller\IFace\IFaceInterface
     * @deprecated Use DI for injecting IFaceFactory instead
     */
    protected function iface_from_model(IFaceModelInterface $model)
    {
        return IFaceFactory::instance()->from_model($model);
    }

    /**
     * @param $name
     * @return \BetaKiller\IFace\Widget\WidgetInterface
     * @deprecated Use DI for injecting WidgetFactory instead
     */
    protected function widget_factory($name)
    {
        return BaseWidget::factory($name);
    }
}
