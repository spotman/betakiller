<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\IFace\Admin\AbstractAdminIFace;
use Psr\Http\Message\ServerRequestInterface;

class ApiTestIFace extends AbstractAdminIFace
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
