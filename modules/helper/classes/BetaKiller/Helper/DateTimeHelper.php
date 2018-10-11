<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

class DateTimeHelper
{
    public static function getUtcTimezone(): \DateTimeZone
    {
        return new \DateTimeZone('UTC');
    }

    public static function getDateTimeFromTimestamp(int $timestamp): \DateTimeImmutable
    {
        $dt = new \DateTimeImmutable;

        return $dt->setTimezone(self::getUtcTimezone())->setTimestamp($timestamp);
    }
}
