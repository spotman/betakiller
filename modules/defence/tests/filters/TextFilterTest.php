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
            'Groß- & Einzelhandel',
            'Test > 5 Kg',
            'Test < 5 Kg',
        ];
    }

    public function sanitizeData(): array
    {
        return [
            ' asd ' => 'asd', // Trim data

            'qwerty'."\0" => 'qwerty', // No NULL-byte
            'qwerty'."\t" => 'qwerty', // No TAB-byte
        ];
    }

    public function invalidData(): array
    {
        return [
            false,
            148,
            [],
            new \stdClass(),

            // No HTML tags allowed
            '<script>alert("Hello")</script> world',
        ];
    }

    protected function makeInstance(): FilterInterface
    {
        return new TextFilter;
    }
}
