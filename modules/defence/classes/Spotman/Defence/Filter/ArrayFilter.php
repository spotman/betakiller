<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

use function is_object;

class ArrayFilter extends AbstractFilterVarFilter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'array';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_SINGLE_ARRAY,
        ];
    }

    /**
     * Apply current filter to provided value
     *
     * @param mixed $value
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function apply($value): array
    {
        // Empty arrays for x-www-form-urlencoded
        if ($value === '[]') {
            $value = [];
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException();
        }

        return $value;
    }
}
