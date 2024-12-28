<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Filter\IntegerFilter;

class IntegerFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        return [
            // Raw integer
            12345,

            // Octal numbers
            0775,

            // Hex numbers
            0xFF,
        ];
    }

    public function sanitizeData(): array
    {
        return [
            // Numeric string to int conversion
            '12345' => 12345,
        ];
    }

    public function invalidData(): array
    {
        return [
            false,
            3.14159,
            'string',
            '01234',
            '1.234',
            '1,234',
            [],
            new \stdClass(),
        ];
    }

    protected function makeInstance(): FilterInterface
    {
        return new IntegerFilter();
    }
}
