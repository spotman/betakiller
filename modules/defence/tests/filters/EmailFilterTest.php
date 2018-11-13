<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\EmailFilter;
use Spotman\Defence\Filter\FilterInterface;

class EmailFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        return [
            // Regular data
            'i.am@mail.com',
            'ya@mail.com',
            // TODO: More examples.
        ];
    }

    /**
     * @return mixed[]
     */
    public function invalidData(): array
    {
        return [
            // Restrict tags
            'ya@mail.com<script>alert("Hello")</script>',

            false,
            148,
            [],
            new \stdClass(),
        ];
    }

    public function sanitizeData(): array
    {
        return [
            ' ya@mail.com ' => 'ya@mail.com', // Trim
            'Ya@Mail.COM'   => 'ya@mail.com', // Lowercase
        ];
    }

    protected function makeInstance(): FilterInterface
    {
        return new EmailFilter;
    }
}
