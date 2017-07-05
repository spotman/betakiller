<?php
namespace BetaKiller\Model;
use BetaKiller\Helper\IFaceHelper;

/**
 * Interface HasPublicReadUrlInterface
 *
 * @package BetaKiller\Helper
 */
interface HasPublicReadUrlInterface
{
    /**
     * @param \BetaKiller\Helper\IFaceHelper $helper
     *
     * @return string
     */
    public function getPublicReadUrl(IFaceHelper $helper): string;
}
