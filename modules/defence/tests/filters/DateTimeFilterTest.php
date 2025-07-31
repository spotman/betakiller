<?php

declare(strict_types=1);

namespace Spotman\Defence\Test;

use DateTimeImmutable;
use DateTimeZone;
use Spotman\Defence\Filter\DateTimeFilter;
use Spotman\Defence\Filter\FilterInterface;

class DateTimeFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        $date = new DateTimeImmutable;

        return [
            $date->format($date::ATOM),
            $date->format($date::ISO8601),
            $date->format('Y-m-d'),
        ];
    }

    public function testPassUnchanged($input = []): void
    {
        // Fake assert to prevent "passUnchanged" checks on DateTimeFilter
        self::assertTrue(true);
    }

    public function passDataUnchanged(): array
    {
        // Filter converts string to DateTimeImmutable
        return [];
    }

    public function sanitizeData(): array
    {
        // Filter converts string to DateTimeImmutable, they can not be compared
        return [
            '2021-07-05T12:13:39.123456+0000' => (new DateTimeImmutable())
                ->setDate(2021, 7, 5)
                ->setTime(12, 13, 39,123456)
                ->setTimezone(new DateTimeZone('UTC')),

            '2024-07-31T14:30:58.123Z' => (new DateTimeImmutable())
                ->setDate(2024, 7, 31)
                ->setTime(14, 30, 58, 123000)
                ->setTimezone(new DateTimeZone('UTC')),
        ];
    }

    public function invalidData(): array
    {
        return [
            false,
            148,
            [],
            new \stdClass(),
        ];
    }

    protected function makeInstance(): FilterInterface
    {
        return new DateTimeFilter;
    }
}
