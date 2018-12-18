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
     * @return string
     * @throws \InvalidArgumentException
     */
    public function apply($value): string
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException;
        }

        $value = strip_tags(str_replace(["\0", "\t", "\r", "\n"], '', $value));

        if ($value === null) {
            throw new \InvalidArgumentException;
        }

        return trim((string)$value);
    }
}
