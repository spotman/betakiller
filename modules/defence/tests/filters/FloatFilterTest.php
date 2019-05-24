<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Filter\FloatFilter;

class FloatFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        return [
            // Integer
            12,

            // Float
            12.345,
        ];
    }

    public function sanitizeData(): array
    {
        return [
            // Cast string to float
            '12.345' => 12.345,
        ];
    }

    public function invalidData(): array
    {
        return [
            false,
            'string',
            [],
            new \stdClass(),
        ];
    }

    protected function makeInstance(): FilterInterface
    {
        return new FloatFilter;
    }
}
