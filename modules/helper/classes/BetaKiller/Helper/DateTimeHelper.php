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
    public static function getNow(DateTimeZone $tz = null): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $tz ?? self::getUtcTimezone());
    }

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
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT,
            self::getUtcTimezone()
        );

        return $fmt->format($time);
    }

    public static function formatDateUser(DateTimeImmutable $time, UserInterface $user, ?int $format = null): string
    {
        return self::formatDateLang($time, $user->getLanguage(), $format);
    }

    public static function formatDateLang(DateTimeImmutable $time, LanguageInterface $lang, ?int $format = null): string
    {
        $fmt = new IntlDateFormatter(
            $lang->getLocale(),
            $format ?? IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            self::getUtcTimezone()
        );

        return $fmt->format($time);
    }

    public static function formatTimeUser(DateTimeImmutable $time, UserInterface $user, ?int $format = null): string
    {
        return self::formatTimeLang($time, $user->getLanguage(), $format);
    }

    public static function formatTimeLang(DateTimeImmutable $time, LanguageInterface $lang, ?int $format = null): string
    {
        $fmt = new IntlDateFormatter(
            $lang->getLocale(),
            IntlDateFormatter::NONE,
            $format ?? IntlDateFormatter::SHORT,
            self::getUtcTimezone()
        );

        return $fmt->format($time);
    }

    public static function createDateTimeFromTimestamp(int $ts): DateTimeImmutable
    {
        return (new DateTimeImmutable())->setTimestamp($ts);
    }

    public static function formatAtom(DateTimeImmutable $time): string
    {
        return $time->format(DateTimeImmutable::ATOM);
    }
}
