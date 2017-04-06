<?php
namespace BetaKiller\Helper;

use BetaKiller\DI\ContainerTrait;
use BetaKiller\IFace\IFaceFactory;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\Widget;

trait IFaceTrait
{
    use ContainerTrait;

    /**
     * @return \URL_Dispatcher
     */
    protected function url_dispatcher()
    {
        return $this->getContainer()->get(\URL_Dispatcher::class);
    }

    /**
     * @return \URL_Parameters
     */
    protected function url_parameters()
    {
        return $this->url_dispatcher()->parameters();
    }

    /**
     * @return \URL_Parameters
     */
    protected function url_parameters_instance()
    {
        return $this->getContainer()->get(\URL_Parameters::class);
    }

    /**
     * @param $codename
     * @return \BetaKiller\IFace\IFaceInterface
     */
    protected function iface_from_codename($codename)
    {
        return IFaceFactory::instance()->from_codename($codename);
    }

    /**
     * @param $model \BetaKiller\IFace\IFaceModelInterface
     * @return \BetaKiller\IFace\IFaceInterface
     */
    protected function iface_from_model(IFaceModelInterface $model)
    {
        return IFaceFactory::instance()->from_model($model);
    }

    /**
     * @param $name
     * @return \BetaKiller\IFace\Widget
     */
    protected function widget_factory($name)
    {
        return Widget::factory($name);
    }
}
