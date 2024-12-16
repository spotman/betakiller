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
            '<script>alert("Hello")</script> world' => '&lt;script&gt;alert(&quot;Hello&quot;)&lt;/script&gt; world',
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
        return new IdentityFilter();
    }
}
