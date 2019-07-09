<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Rule\DefinitionRuleInterface;
use Spotman\Defence\Rule\LengthBetweenRule;

class LengthBetweenRuleTest extends AbstractRuleTest
{
    /**
     * Array of valid values
     *
     * @return mixed[]
     */
    public function validRequiredData(): array
    {
        return [
            'asd',
            'asdqweasd',
            \array_fill(0, 1, 'a'),
            \array_fill(0, 2, 'a'),
            \array_fill(0, 5, 'a'),
            \array_fill(0, 10, 'a'),
        ];
    }

    /**
     * Array of invalid values
     *
     * @return mixed[]
     */
    public function invalidData(): array
    {
        return [
            '', // Too short
            \str_repeat('z', 11), // Too long
            [],
            \array_fill(0, 11, 'a'),
        ];
    }

    /**
     * Array of invalid arguments
     *
     * @return mixed[]
     */
    public function invalidArgumentData(): array
    {
        return [
            true,
            100,
            0xFF,
            new \stdClass,
        ];
    }

    protected function makeInstance(): DefinitionRuleInterface
    {
        return new LengthBetweenRule(1, 10);
    }
}
