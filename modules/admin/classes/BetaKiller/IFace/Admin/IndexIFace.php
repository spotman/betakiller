<?php

namespace BetaKiller\IFace\Admin;

use Psr\Http\Message\ServerRequestInterface;

final readonly class IndexIFace extends AbstractAdminIFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        return [];
    }
}
