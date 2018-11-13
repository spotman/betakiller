<?php
namespace BetaKiller\IFace\Admin\Content;

use Psr\Http\Message\ServerRequestInterface;

class PostCreateIFace extends AbstractContentAdminIFace
{
    /**
     * Returns data for View
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
