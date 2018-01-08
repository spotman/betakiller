<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\IFace\AbstractIFace;

abstract class AbstractAdminBase extends AbstractIFace
{
    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before(): void
    {
        // Disable caching
        $this->ifaceHelper->setExpiresInPast($this);

        parent::before();
    }
}
