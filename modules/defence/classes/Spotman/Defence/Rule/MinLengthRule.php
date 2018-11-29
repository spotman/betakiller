<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

class MinLengthRule extends AbstractLengthRule
{
    /**
     * @var int
     */
    private $min;

    /**
     * MinLengthRule constructor.
     *
     * @param int $min
     */
    public function __construct(int $min)
    {
        if ($min < 0) {
            throw new \InvalidArgumentException('Min count must be grater than zero');
        }

        $this->min = $min;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'minLength';
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        $length = $this->getLength($value);

        return $length >= $this->min;
    }
}
