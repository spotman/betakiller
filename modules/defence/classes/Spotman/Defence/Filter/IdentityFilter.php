<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

class IdentityFilter extends AbstractFilterVarFilter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'identity';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_IDENTITY,
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
            \FILTER_SANITIZE_SPECIAL_CHARS,
            \FILTER_FLAG_STRIP_LOW + \FILTER_FLAG_STRIP_HIGH + \FILTER_FLAG_STRIP_BACKTICK
        );
    }
}
