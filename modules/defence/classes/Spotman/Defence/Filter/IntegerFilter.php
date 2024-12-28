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
        // Allow numeric strings
        if (is_string($value)) {
            $strValue = $value;
            $intValue = (int)$value;

            // Check if string representation is equal to integer one
            if ((string)$intValue !== $strValue) {
                throw new InvalidArgumentException();
            }

            $value = $intValue;
        }

        if (!is_int($value)) {
            throw new InvalidArgumentException();
        }

        $value = $this->filterVar(
            $value,
            \FILTER_VALIDATE_INT,
            \FILTER_FLAG_ALLOW_OCTAL | \FILTER_FLAG_ALLOW_HEX
        );

        if ($value === null) {
            throw new InvalidArgumentException();
        }

        return (int)$value;
    }
}
