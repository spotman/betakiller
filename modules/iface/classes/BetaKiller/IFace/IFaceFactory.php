<?php
namespace BetaKiller\IFace;

use BetaKiller\DI\Container;
use BetaKiller\Utils\Instance\Simple;

class IFaceFactory
{
    use Simple;

    /**
     * Creates instance of IFace from model
     *
     * @param \IFace_Model $model
     * @return IFace
     */
    public function from_model(\IFace_Model $model)
    {
        return $this->get_provider()->from_model($model);
    }

    /**
     * Creates IFace instance from it`s codename
     *
     * @param string $codename IFace codename
     * @return IFace
     * @throws \IFace_Exception
     */
    public function from_codename($codename)
    {
        return $this->get_provider()->by_codename($codename);
    }

    protected function get_provider()
    {
        return Container::instance()->get(\IFace_Provider::class);
    }
}
