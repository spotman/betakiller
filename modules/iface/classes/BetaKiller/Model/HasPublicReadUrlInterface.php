<?php
namespace BetaKiller\Model;

use BetaKiller\Helper\UrlHelper;

/**
 * Interface HasPublicReadUrlInterface
 *
 * @package BetaKiller\Helper
 */
interface HasPublicReadUrlInterface
{
    /**
     * @param \BetaKiller\Helper\UrlHelper $helper
     *
     * @return string
     */
    public function getPublicReadUrl(UrlHelper $helper): string;
}
