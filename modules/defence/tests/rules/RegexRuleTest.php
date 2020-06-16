<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Rule\DefinitionRuleInterface;
use Spotman\Defence\Rule\RegexRule;

class RegexRuleTest extends AbstractRuleTest
{
    /**
     * Array of valid values
     *
     * @return mixed[]
     */
    public function validRequiredData(): array
    {
        return [
            '7',
            '6.0',
            '2.3',
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
            '', // Empty
            'v2+', // Wrong symbols
            'v2.1', // Wrong symbols
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
        return new RegexRule('/^[\d.]+$/');
    }
}
