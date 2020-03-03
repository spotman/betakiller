<?php
declare(strict_types=1);

namespace BetaKiller\HitStat;

use BetaKiller\Model\HitInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Zend\Expressive\Session\SessionInterface;

class HitStatSessionHelper
{
    private const HIT_KEY = 'first_hit_uuid';

    public static function setFirstHitUuid(SessionInterface $session, UuidInterface $uuid): void
    {
        $session->set(self::HIT_KEY, $uuid->toString());
    }

    public static function hasFirstHitUuid(SessionInterface $session): bool
    {
        return $session->has(self::HIT_KEY);
    }

    public static function getFirstHitUuid(SessionInterface $session): UuidInterface
    {
        $value = $session->get(self::HIT_KEY);

        return Uuid::fromString($value);
    }
}
