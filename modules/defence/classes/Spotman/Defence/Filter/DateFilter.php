<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Spotman\Defence\ArgumentDefinitionInterface;
use Throwable;

use function is_string;

readonly class DateFilter implements FilterInterface
{
    public function __construct(private ?DateTimeZone $tz = null)
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'date';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_DATE,
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
    public function apply($value): DateTimeImmutable
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Datetime must be a string');
        }

        try {
            return DateTimeImmutable::createFromFormat('Y-m-d', $value, $this->tz)->setTime(0, 0);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('String is not a valid date', 0, $e);
        }
    }
}
