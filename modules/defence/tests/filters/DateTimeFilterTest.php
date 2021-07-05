<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\DateTimeFilter;
use Spotman\Defence\Filter\FilterInterface;

class DateTimeFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        $date = new \DateTimeImmutable;

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
        $text = '2021-07-05T12:13:39.000000+0000';
        $date = new \DateTimeImmutable($text);

        // Filter converts string to DateTimeImmutable, they can not be compared
        return [
            $text => $date,
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
