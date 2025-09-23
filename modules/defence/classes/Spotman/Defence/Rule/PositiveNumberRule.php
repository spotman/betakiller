<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

use InvalidArgumentException;
use Spotman\Defence\ArgumentDefinitionInterface;

class PositiveNumberRule implements DefinitionRuleInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'positiveNumber';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_INTEGER,
            ArgumentDefinitionInterface::TYPE_FLOAT,
        ];
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        if (!is_int($value) && !is_float($value)) {
            throw new InvalidArgumentException;
        }

        return $value > 0;
    }
}
