<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Filter\HtmlFilter;

class HtmlFilterTest extends AbstractFilterTest
{
    /**
     * @return mixed[]
     */
    public function passData(): array
    {
        return [
            'qwerty',
            'qwerty'."\r\n".'qwerty', // Allow EOL
            'qwerty with "double quotes"',
            "qwerty with 'single quotes'",
            'qwerty with `backtick',
            '<script>alert("Hello")</script> world',
        ];
    }

    public function sanitizeData(): array
    {
        return [
            'qwerty'."\0" => 'qwerty', // No NULL-byte
            'qwerty'."\t" => 'qwerty', // No tabs
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
        return new HtmlFilter;
    }
}
