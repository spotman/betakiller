<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

class MaxLengthRule extends AbstractLengthRule
{
    /**
     * @var int
     */
    private $max;

    /**
     * MaxLengthRule constructor.
     *
     * @param int $max
     */
    public function __construct(int $max)
    {
        if ($max < 0) {
            throw new \InvalidArgumentException('Max count must be grater than zero');
        }

        $this->max = $max;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'maxLength';
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        $length = $this->getLength($value);

        return $length <= $this->max;
    }
}
