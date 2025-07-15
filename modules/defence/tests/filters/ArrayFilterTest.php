<?php

declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\ArrayFilter;
use Spotman\Defence\Filter\FilterInterface;

class ArrayFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        return [
            [1, 2],
            ['one', 'two'],
        ];
    }

    public function sanitizeData(): array
    {
        return [
            '[]' => [],
        ];
    }

    public function invalidData(): array
    {
        return [
            true,
            false,

            // XSS Injection
            '[]<script>alert("message")</script>',

            // Invalid string values
            'ok',
            'ya',

            // Other types
            12345,
            new \stdClass(),
        ];
    }

    protected function makeInstance(): FilterInterface
    {
        return new ArrayFilter();
    }
}
