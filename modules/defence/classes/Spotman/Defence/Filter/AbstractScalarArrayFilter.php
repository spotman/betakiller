<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\ArgumentDefinitionInterface;

abstract class AbstractScalarArrayFilter implements FilterInterface
{
    /**
     * @var \Spotman\Defence\Filter\FilterInterface
     */
    private $proxy;

    /**
     * AbstractScalarArrayFilter constructor.
     *
     * @param \Spotman\Defence\Filter\FilterInterface $proxy
     */
    public function __construct(FilterInterface $proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_SINGLE_ARRAY,
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
        if (!\is_array($value)) {
            throw new \InvalidArgumentException(sprintf('Value must be an array but "%s" given', \gettype($value)));
        }

        if (\is_string(\key($value))) {
            throw new \InvalidArgumentException('Only indexed arrays are supported, use composite for nested arguments');
        }

        $output = [];

        foreach ($value as $index => $item) {
            $output[$index] = $this->proxy->apply($item);
        }

        return $output;
    }
}
