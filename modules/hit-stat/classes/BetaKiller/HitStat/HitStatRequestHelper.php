<?php
declare(strict_types=1);

namespace BetaKiller\HitStat;

use BetaKiller\Model\HitInterface;
use Psr\Http\Message\ServerRequestInterface;

class HitStatRequestHelper
{
    public static function withHit(ServerRequestInterface $request, HitInterface $hit): ServerRequestInterface
    {
        return $request->withAttribute(HitInterface::class, $hit);
    }

    public static function getHit(ServerRequestInterface $request): ?HitInterface
    {
        return $request->getAttribute(HitInterface::class);
    }
}
