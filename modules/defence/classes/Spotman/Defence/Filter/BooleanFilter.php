<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

class BooleanFilter extends AbstractFilterVarFilter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'boolean';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_BOOLEAN,
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
    public function apply($value): bool
    {
        $value = $this->filterVar(
            $value,
            \FILTER_VALIDATE_BOOLEAN,
            \FILTER_NULL_ON_FAILURE
        );

        if ($value === null) {
            throw new \InvalidArgumentException;
        }

        return (bool)$value;
    }
}
