<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

class EmailFilter extends AbstractFilterVarFilter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'email';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_EMAIL,
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
        if (!\is_string($value)) {
            throw new \InvalidArgumentException;
        }

        $value = $this->filterVar(
            trim($value), // Remove spaces
            \FILTER_VALIDATE_EMAIL,
            \FILTER_FLAG_EMAIL_UNICODE
        );

        if ($value === null) {
            throw new \InvalidArgumentException;
        }

        // Lowercase for simplicity
        return \mb_strtolower($value);
    }
}
