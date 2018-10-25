<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class Index extends AbstractIFace
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string[]
     */
    public function getData(ServerRequestInterface $request): array
    {
        return [];
    }
}
