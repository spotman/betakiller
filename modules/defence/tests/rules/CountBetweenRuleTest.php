<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Rule\CountBetweenRule;
use Spotman\Defence\Rule\DefinitionRuleInterface;

class CountBetweenRuleTest extends AbstractRuleTest
{
    /**
     * Array of valid values
     *
     * @return mixed[]
     */
    public function validData(): array
    {
        return [
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
            'string',
            new \stdClass,
        ];
    }

    protected function makeInstance(): DefinitionRuleInterface
    {
        return new CountBetweenRule(1, 10);
    }
}
