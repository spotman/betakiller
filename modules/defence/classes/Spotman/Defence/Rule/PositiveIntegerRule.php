<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

use Spotman\Defence\ArgumentDefinitionInterface;

class PositiveIntegerRule implements DefinitionRuleInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'positiveInteger';
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
     * @param mixed $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        if (!\is_int($value)) {
            throw new \InvalidArgumentException;
        }

        return $value > 0;
    }
}
