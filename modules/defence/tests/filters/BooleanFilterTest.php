<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\BooleanFilter;
use Spotman\Defence\Filter\FilterInterface;

class BooleanFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        return [
            true,
            false,
        ];
    }

    public function sanitizeData(): array
    {
        return [
            // String representation
            'true' => true,
            'false' => false,
        ];
    }

    public function invalidData(): array
    {
        return [
            // XSS Injection
            '<script>alert("message")</script>true',

            // Invalid string values
            'ok',
            'ya',

            // Other types
            12345,
            [],
            new \stdClass(),
        ];
    }

    protected function makeInstance(): FilterInterface
    {
        return new BooleanFilter;
    }
}
