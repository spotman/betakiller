<?php
namespace BetaKiller\Helper;

use BetaKiller\DI\ContainerTrait;
use BetaKiller\IFace\IFaceFactory;

/**
 * Trait IFaceHelperTrait
 *
 * @package BetaKiller\Helper
 * @deprecated
 */
trait IFaceHelperTrait
{
    use ContainerTrait;

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
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @deprecated Use DI for injecting IFaceFactory instead
     */
    protected function iface_from_codename($codename)
    {
        $factory = $this->getContainer()->get(IFaceFactory::class);

        return $factory->fromCodename($codename);
    }
}
