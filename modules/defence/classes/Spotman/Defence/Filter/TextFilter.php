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
        return 'text';
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

        if (strip_tags($value) !== $value) {
            throw new \InvalidArgumentException('HTML tags are not allowed');
        }

        return trim((string)$value);
    }
}
