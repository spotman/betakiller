<?php

declare(strict_types=1);

namespace Spotman\Defence\Test;

use DateTimeImmutable;
use Spotman\Defence\Filter\DateFilter;
use Spotman\Defence\Filter\FilterInterface;
use stdClass;

class DateFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        $date = new DateTimeImmutable();

        return [
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
        $text = '2021-07-05';
        $date = (new DateTimeImmutable($text))->setTime(0, 0);

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
            new stdClass(),
        ];
    }

    public function testTimeZone(): void
    {
        $tz     = new \DateTimeZone('Europe/Berlin');
        $filter = new DateFilter($tz);

        $text = '2021-07-05';
        $date = (new DateTimeImmutable($text, $tz))->setTime(0, 0);

        $this->assertEquals($date, $filter->apply($text));
    }

    protected function makeInstance(): FilterInterface
    {
        return new DateFilter();
    }
}
