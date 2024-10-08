<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Rule\DefinitionRuleInterface;
use Spotman\Defence\Rule\WhitelistRule;

class WhitelistRuleTest extends AbstractRuleTest
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
            'qwe',
            123,
            456,
            0xffee,
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
            'zxc',
            789,
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
            [],
            new \stdClass,
        ];
    }

    protected function makeInstance(): DefinitionRuleInterface
    {
        return new WhitelistRule([
            'asd',
            'qwe',
            123,
            456,
            0xffee,
        ]);
    }
}
