<?php

declare(strict_types=1);

namespace Spotman\Defence\Filter;

use InvalidArgumentException;
use Spotman\Defence\ArgumentDefinitionInterface;

use function is_int;

class ParameterFilter extends AbstractFilterVarFilter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'parameter';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_PARAMETER,
        ];
    }

    /**
     * Apply current filter to provided value
     *
     * @param mixed $value
     *
     * @return int|string
     */
    public function apply($value): int|string
    {
        if (!is_int($value) && !is_string($value)) {
            throw new InvalidArgumentException();
        }

        $intValue = (int)$value;

        // Allow integers to be passed as strings
        // Check if string representation is equal to integer one
        if ((string)$intValue === $value) {
            $value = $intValue;
        }

        // Do not allow other numeric strings (floats, money formatted, etc)
        if (is_string($value) && is_numeric($value)) {
            throw new InvalidArgumentException();
        }

        return $value;
    }
}
