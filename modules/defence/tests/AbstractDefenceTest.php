<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use BetaKiller\Test\AbstractTestCase;
use Spotman\Defence\ArgumentDefinitionInterface;
use Spotman\Defence\CompositeArgumentDefinition;
use Spotman\Defence\SingleArgumentDefinition;

abstract class AbstractDefenceTest extends AbstractTestCase
{
    protected function createArgumentDefinitionFromValue(string $name, $value): ArgumentDefinitionInterface
    {
        switch (true) {
            case \is_bool($value):
                return new SingleArgumentDefinition($name, ArgumentDefinitionInterface::TYPE_BOOLEAN);

            case \is_string($value):
                return new SingleArgumentDefinition($name, ArgumentDefinitionInterface::TYPE_STRING);

            case \is_int($value):
                return new SingleArgumentDefinition($name, ArgumentDefinitionInterface::TYPE_INTEGER);

            case \is_array($value):
                return new SingleArgumentDefinition($name, ArgumentDefinitionInterface::TYPE_SINGLE_ARRAY);

            case \is_object($value):
                return new CompositeArgumentDefinition($name);

            default:
                throw new \LogicException('Unknown value type '.\gettype($value));
        }
    }
}
