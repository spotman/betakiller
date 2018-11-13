<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Filter\LowercaseFilter;

class LowercaseFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        return [
            'qwerty',
        ];
    }

    public function sanitizeData(): array
    {
        return [
            'QWERTY' => 'qwerty',
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
        return new LowercaseFilter;
    }
}
