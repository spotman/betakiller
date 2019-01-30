<?php
declare(strict_types=1);

namespace BetaKiller\HitStat;

use BetaKiller\Model\HitInterface;
use Zend\Expressive\Session\SessionInterface;

class HitStatSessionHelper
{
    private const HIT_KEY = 'first_hit';

    public static function setFirstHit(SessionInterface $session, HitInterface $hit): void
    {
        $session->set(self::HIT_KEY, $hit->getID());
    }

    public static function hasFirstHit(SessionInterface $session): bool
    {
        return $session->has(self::HIT_KEY);
    }

    public static function getFirstHitID(SessionInterface $session): string
    {
        return $session->get(self::HIT_KEY);
    }
}
