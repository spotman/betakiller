<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

class LengthBetweenRule extends AbstractLengthRule
{
    /**
     * @var \Spotman\Defence\Rule\MinLengthRule
     */
    private $min;

    /**
     * @var \Spotman\Defence\Rule\MaxLengthRule
     */
    private $max;

    /**
     * LengthBetweenRule constructor.
     *
     * @param int $min
     * @param int $max
     */
    public function __construct(int $min, int $max)
    {
        if ($min >= $max) {
            throw new \InvalidArgumentException('Min count must be grater than max count');
        }

        $this->min = new MinLengthRule($min);
        $this->max = new MaxLengthRule($max);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'lengthBetween';
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        return $this->min->check($value) && $this->max->check($value);
    }
}
