<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

class HtmlFilter extends AbstractFilterVarFilter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'html';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_HTML,
        ];
    }

    /**
     * Apply current filter to provided value
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function apply($value): string
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException;
        }

        $value = str_replace(["\0", "\t"], '', $value);

        $value = $this->filterVar(
            $value,
            \FILTER_UNSAFE_RAW,
            \FILTER_FLAG_NO_ENCODE_QUOTES
        );

        if ($value === null) {
            throw new \InvalidArgumentException;
        }

        return $value;
    }
}
