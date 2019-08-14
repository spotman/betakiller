<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\FilterInterface;

trait ArgumentWithFiltersTrait
{
    /**
     * @var \Spotman\Defence\Filter\FilterInterface[]
     */
    private $filters = [];

    /**
     * @param \Spotman\Defence\Filter\FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter): void
    {
        $name = $filter->getName();

        if (isset($this->rules[$name])) {
            throw new \LogicException(sprintf('Duplicate filter "%s" for argument "%s"', $name, $this->getName()));
        }

        $this->filters[$name] = $filter;
    }

    /**
     * @return \Spotman\Defence\Filter\FilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
