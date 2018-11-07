<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

class StringFilter extends AbstractFilterVarFilter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'string';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_STRING,
        ];
    }

    /**
     * Apply current filter to provided value
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function apply($value)
    {
        return $this->filterVar(
            $value,
            \FILTER_SANITIZE_STRING,
            \FILTER_FLAG_STRIP_LOW + \FILTER_FLAG_STRIP_HIGH
        );
    }
}
