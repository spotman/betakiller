<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Rule\DefinitionRuleInterface;
use Spotman\Defence\Rule\PositiveIntegerRule;

class PositiveIntegerRuleTest extends AbstractRuleTest
{
    /**
     * Array of valid values
     *
     * @return mixed[]
     */
    public function validRequiredData(): array
    {
        return [
            1,
            10,
            1000000,
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
            0,
            -1,
            -100,
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
            'string',
            [],
            new \stdClass,
        ];
    }

    protected function makeInstance(): DefinitionRuleInterface
    {
        return new PositiveIntegerRule;
    }
}
