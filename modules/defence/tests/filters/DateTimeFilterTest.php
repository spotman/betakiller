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
        ];
    }

    public function sanitizeData(): array
    {
        return [];
    }

    public function invalidData(): array
    {
        $date = new \DateTimeImmutable;

        return [
            $date->format($date::ISO8601),
            $date->format('Y-m-d'),
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
