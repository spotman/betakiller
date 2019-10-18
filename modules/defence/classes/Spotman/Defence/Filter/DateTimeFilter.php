<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

class DateTimeFilter implements FilterInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'datetime';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_DATETIME,
        ];
    }

    /**
     * Apply current filter to provided value
     *
     * @param mixed $value
     *
     * @return \DateTimeImmutable
     * @throws \InvalidArgumentException
     */
    public function apply($value): \DateTimeImmutable
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException('Datetime must be a string');
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('String is not valid datetime', 0, $e);
        }
    }
}
