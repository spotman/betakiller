<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Model\UserInterface;
use DateTimeImmutable;
use DateTimeZone;
use IntlDateFormatter;

class DateTimeHelper
{
    public static function getUtcTimezone(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    public static function getDateTimeFromTimestamp(int $timestamp): DateTimeImmutable
    {
        return (new DateTimeImmutable)->setTimezone(self::getUtcTimezone())->setTimestamp($timestamp);
    }

    public static function formatDateTime(DateTimeImmutable $time, UserInterface $user): string
    {
        $fmt = new IntlDateFormatter(
            $user->getLanguage()->getLocale(),
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            self::getUtcTimezone()
        );

        return $fmt->format($time);
    }
}
