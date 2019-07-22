<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Model\LanguageInterface;
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

    public static function formatDateTimeUser(DateTimeImmutable $time, UserInterface $user): string
    {
        return self::formatDateTimeLang($time, $user->getLanguage());
    }

    public static function formatDateTimeLang(DateTimeImmutable $time, LanguageInterface $lang): string
    {
        $fmt = new IntlDateFormatter(
            $lang->getLocale(),
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            self::getUtcTimezone()
        );

        return $fmt->format($time);
    }

    public static function formatDateUser(DateTimeImmutable $time, UserInterface $user): string
    {
        return self::formatDateLang($time, $user->getLanguage());
    }

    public static function formatDateLang(DateTimeImmutable $time, LanguageInterface $lang): string
    {
        $fmt = new IntlDateFormatter(
            $lang->getLocale(),
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            self::getUtcTimezone()
        );

        return $fmt->format($time);
    }

    public static function formatTimeUser(DateTimeImmutable $time, UserInterface $user): string
    {
        return self::formatTimeLang($time, $user->getLanguage());
    }

    public static function formatTimeLang(DateTimeImmutable $time, LanguageInterface $lang): string
    {
        $fmt = new IntlDateFormatter(
            $lang->getLocale(),
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT,
            self::getUtcTimezone()
        );

        return $fmt->format($time);
    }

    public static function createDateTimeFromTimestamp(int $ts): DateTimeImmutable
    {
        return (new DateTimeImmutable())->setTimestamp($ts);
    }
}
