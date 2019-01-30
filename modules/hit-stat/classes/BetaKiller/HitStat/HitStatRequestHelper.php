<?php
declare(strict_types=1);

namespace BetaKiller\HitStat;

use BetaKiller\Model\HitInterface;
use Psr\Http\Message\ServerRequestInterface;

class HitStatRequestHelper
{
    private const HIT_KEY = HitInterface::class;

    public static function setHit(ServerRequestInterface $request, HitInterface $hit): ServerRequestInterface
    {
        return $request->withAttribute(self::HIT_KEY, $hit);
    }

    public static function getHit(ServerRequestInterface $request): HitInterface
    {
        return $request->getAttribute(self::HIT_KEY);
    }

    public static function hasHit(ServerRequestInterface $request): bool
    {
        return (bool)$request->getAttribute(self::HIT_KEY);
    }
}
