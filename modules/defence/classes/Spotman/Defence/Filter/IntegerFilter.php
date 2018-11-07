<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

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
    public function apply($value)
    {
        return $this->filterVar(
            $value,
            \FILTER_VALIDATE_INT,
            \FILTER_FLAG_ALLOW_OCTAL | \FILTER_FLAG_ALLOW_HEX
        );
    }
}
