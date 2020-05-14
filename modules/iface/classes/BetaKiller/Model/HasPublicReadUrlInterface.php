<?php
namespace BetaKiller\Model;

use BetaKiller\Helper\UrlHelperInterface;

/**
 * Interface HasPublicReadUrlInterface
 *
 * @package BetaKiller\Helper
 */
interface HasPublicReadUrlInterface
{
    /**
     * @param \BetaKiller\Helper\UrlHelperInterface $helper
     *
     * @return string
     */
    public function getPublicReadUrl(UrlHelperInterface $helper): string;
}
