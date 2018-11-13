<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Filter\TextFilter;

class TextFilterTest extends AbstractFilterTest
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
        ];
    }

    public function sanitizeData(): array
    {
        return [
            ' asd ' => 'asd', // Trim data

            'qwerty'."\0" => 'qwerty', // No NULL-byte

            '<script>alert("Hello")</script> world' => 'alert("Hello") world', // Remove tags
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
        return new TextFilter;
    }
}
