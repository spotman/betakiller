<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

use Spotman\Defence\ArgumentDefinitionInterface;

abstract class AbstractLengthRule implements DefinitionRuleInterface
{
    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_STRING,
            ArgumentDefinitionInterface::TYPE_TEXT,
            ArgumentDefinitionInterface::TYPE_HTML,
            ArgumentDefinitionInterface::TYPE_SINGLE_ARRAY,
            ArgumentDefinitionInterface::TYPE_COMPOSITE_ARRAY,
        ];
    }

    protected function getLength($value): int
    {
        switch (true) {
            case \is_array($value):
                return \count($value);

            case \is_string($value):
                return \mb_strlen($value);

            default:
                throw new \InvalidArgumentException;
        }
    }
}
