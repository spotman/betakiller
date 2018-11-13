<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAdminIFace extends AbstractIFace
{
    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Exception
     */
    public function before(ServerRequestInterface $request): void
    {
        // Disable caching for admin ifaces
        $this->setExpiresInPast();

        parent::before($request);
    }
}
