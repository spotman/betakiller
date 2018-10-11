<?php
declare(strict_types=1);

namespace BetaKiller\Model;

trait SecondaryUrlElementModelTrait
{
    /**
     * Returns TRUE if current URL element is hidden in sitemap
     *
     * @return bool
     */
    public function isHiddenInSiteMap(): bool
    {
        // Webhooks are always hidden in sitemap
        return true;
    }
}
