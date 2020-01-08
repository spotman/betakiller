<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\UserInterface;
use DateTimeImmutable;
use DateTimeZone;
use IntlDateFormatter;

final class DateTimeHelper
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

    public static function formatDateUser(DateTimeImmutable $date, UserInterface $user, ?int $format = null): string
    {
        return self::formatDateLang($date, $user->getLanguage(), $format);
    }

    public static function formatDateLang(DateTimeImmutable $date, LanguageInterface $lang, ?int $format = null): string
    {
        $fmt = new IntlDateFormatter(
            $lang->getLocale(),
            $format ?? IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            self::getUtcTimezone()
        );

        return $fmt->format($date);
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

    public static function formatJsDate(DateTimeImmutable $time): string
    {
        return self::formatAtom($time);
    }

    /**
     * Compare dates only (ignore time)
     *
     * @param \DateTimeImmutable $left
     * @param \DateTimeImmutable $right
     *
     * @return bool
     */
    public static function isSameDate(DateTimeImmutable $left, DateTimeImmutable $right): bool
    {
        return $left->setTime(0, 0, 0, 0) == $right->setTime(0, 0, 0, 0);
    }
}
