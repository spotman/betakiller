<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

class TextFilter extends AbstractFilterVarFilter
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
            ArgumentDefinitionInterface::TYPE_TEXT,
        ];
    }

    /**
     * Apply current filter to provided value
     *
     * @param mixed $value
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function apply($value): string
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException;
        }

        $value = str_replace(["\0", "\t"], '', $value);

        $value = $this->filterVar(
            $value,
            \FILTER_SANITIZE_STRING,
            \FILTER_FLAG_STRIP_HIGH + \FILTER_FLAG_NO_ENCODE_QUOTES
        );

        if ($value === null) {
            throw new \InvalidArgumentException;
        }

        return trim((string)$value);
    }
}
