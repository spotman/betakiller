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
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     * @return \BetaKiller\IFace\IFaceInterface
     */
    public function from_model(IFaceModelInterface $model)
    {
        return $this->get_provider()->fromModel($model);
    }

    /**
     * Creates IFace instance from it`s codename
     *
     * @param string $codename IFace codename
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function from_codename($codename)
    {
        return $this->get_provider()->fromCodename($codename);
    }

    /**
     * @return \BetaKiller\IFace\IFaceProvider
     */
    protected function get_provider()
    {
        return Container::getInstance()->get(\BetaKiller\IFace\IFaceProvider::class);
    }
}
