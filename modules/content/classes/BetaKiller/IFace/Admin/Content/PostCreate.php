<?php
namespace BetaKiller\IFace\Admin\Content;

use Psr\Http\Message\ServerRequestInterface;

class PostCreate extends AbstractAdminBase
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
