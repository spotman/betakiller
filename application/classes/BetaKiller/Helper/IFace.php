<?php
namespace BetaKiller\Helper;

trait IFace
{
    /**
     * @return \URL_Dispatcher
     */
    final protected function url_dispatcher()
    {
        return \URL_Dispatcher::instance();
    }

    /**
     * @return \URL_Parameters
     */
    final protected function url_parameters()
    {
        return $this->url_dispatcher()->parameters();
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
}
