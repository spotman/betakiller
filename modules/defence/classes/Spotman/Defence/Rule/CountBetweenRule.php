<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

use Spotman\Defence\ArgumentDefinitionInterface;

class CountBetweenRule implements DefinitionRuleInterface
{
    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * CountBetweenRule constructor.
     *
     * @param int $min
     * @param int $max
     */
    public function __construct(int $min, int $max)
    {
        if ($min < 0) {
            throw new \InvalidArgumentException('Min count must be grater than zero');
        }

        if ($max < 0) {
            throw new \InvalidArgumentException('Max count must be grater than zero');
        }

        if ($min >= $max) {
            throw new \InvalidArgumentException('Min count must be grater than max count');
        }

        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'countBetween';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_ARRAY,
        ];
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        $count = \count($value);

        return $count >= $this->min && $count <= $this->max;
    }
}
