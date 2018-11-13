<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Filter\IdentityFilter;

class IdentityFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        return [
            // Regular values
            '12345',
            'codename',
        ];
    }

    public function sanitizeData(): array
    {
        return [
            // Remove tags
            '<script>alert("Hello")</script> world' => 'alert(&#34;Hello&#34;) world',
        ];
    }

    /**
     * @return mixed[]
     */
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
        return new IdentityFilter;
    }
}
