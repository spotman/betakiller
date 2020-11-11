<?php
declare(strict_types=1);

namespace BetaKiller\Model;

trait HiddenInSitemapUrlElementModelTrait
{
    /**
     * Returns TRUE if current URL element is hidden in sitemap
     *
     * @return bool
     */
    public function isHiddenInSiteMap(): bool
    {
        // Webhooks and Actions are always hidden in sitemap
        return true;
    }
}
