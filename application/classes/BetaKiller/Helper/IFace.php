<?php
namespace BetaKiller\Helper;

use BetaKiller\DI;

trait IFace
{
    use DI\Mixin;

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
     * @return \BetaKiller\IFace\IFace
     */
    protected function iface_by_codename($codename)
    {
        return \BetaKiller\IFace\IFace::by_codename($codename);
    }

    /**
     * @param $model \IFace_Model
     * @return \BetaKiller\IFace\IFace
     */
    protected function iface_factory(\IFace_Model $model)
    {
        return \BetaKiller\IFace\IFace::factory($model);
    }

    /**
     * @param $name
     * @return \BetaKiller\IFace\Widget
     */
    protected function widget_factory($name)
    {
        return \BetaKiller\IFace\Widget::factory($name);
    }
}
