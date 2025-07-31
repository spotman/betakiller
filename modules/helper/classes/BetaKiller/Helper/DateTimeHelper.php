<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\UserInterface;
use DateInterval;
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

    public static function getServerTimezone(): DateTimeZone
    {
        return new DateTimeZone(date_default_timezone_get());
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
            IntlDateFormatter::RELATIVE_MEDIUM,
            IntlDateFormatter::SHORT,
            $time->getTimezone() ?: self::getUtcTimezone()
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
            $format ?? IntlDateFormatter::RELATIVE_MEDIUM,
            IntlDateFormatter::NONE,
            $date->getTimezone() ?: self::getUtcTimezone()
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
            $time->getTimezone() ?: self::getUtcTimezone()
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
        return $time->format(DateTimeImmutable::RFC3339_EXTENDED);
    }

    public static function createFromJsDate(string $value): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat(DateTimeImmutable::RFC3339_EXTENDED, $value);
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

    /**
     * Detects timezones where local time is equal to provided data
     *
     * @param int $hour
     * @param int $minute
     *
     * @return array<string, DateTimeZone>
     * @throws \Exception
     */
    public static function localTimeToTimezones(int $hour, int $minute): array
    {
        $tzList = [];

        $deviation = new DateInterval('PT16M');

        foreach (DateTimeZone::listIdentifiers() as $tzName) {
            $tz    = new DateTimeZone($tzName);
            $tzNow = new DateTimeImmutable('now', $tz);

            $tzCheck  = $tzNow->setTime($hour, $minute);
            $tzAfter  = $tzCheck->sub($deviation);
            $tzBefore = $tzCheck->add($deviation);

            if ($tzNow >= $tzAfter && $tzNow <= $tzBefore) {
                $tzList[$tzName] = $tz;
            }
        }

        return $tzList;
    }

    public static function dateIntervalToSeconds(DateInterval $interval): int
    {
        $now = new DateTimeImmutable();
        $ref = $now->add($interval);

        return abs($ref->getTimestamp() - $now->getTimestamp());
    }
}
