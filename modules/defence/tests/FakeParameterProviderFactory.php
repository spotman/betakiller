<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\ArgumentDefinitionInterface;
use Spotman\Defence\Parameter\ArgumentParameterInterface;
use Spotman\Defence\Parameter\ParameterProviderFactoryInterface;
use Spotman\Defence\Parameter\ParameterProviderInterface;

final class FakeParameterProviderFactory implements ParameterProviderFactoryInterface
{
    public function convertValue($value): ArgumentParameterInterface
    {
        if (!\is_string($value)) {
            throw new \LogicException('Test value must be "string"');
        }

        return new FakeStringParameter($value);
    }

    public function createFor(ArgumentDefinitionInterface $argDef): ParameterProviderInterface
    {
        switch ($argDef->getType()) {
            case ArgumentDefinitionInterface::TYPE_PARAMETER:
                return new FakeStringParameterProvider();

            default:
                throw new \LogicException(sprintf('Unknown fake argument type "%s"', $argDef->getType()));
        }
    }
}
