<?php

declare(strict_types=1);

namespace Spotman\Defence\Filter;

use InvalidArgumentException;
use Spotman\Defence\ArgumentDefinitionInterface;

use function is_int;

class IntegerFilter extends AbstractFilterVarFilter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'integer';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_INTEGER,
        ];
    }

    /**
     * Apply current filter to provided value
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function apply($value): int
    {
        // Allow integers to be passed as strings
        if (is_string($value)) {
            $intValue = (int)$value;

            // Check if string representation is equal to integer one
            if ((string)$intValue === $value) {
                $value = $intValue;
            }
        }

        if (!is_int($value)) {
            throw new InvalidArgumentException();
        }

        return (int)$value;
    }
}
