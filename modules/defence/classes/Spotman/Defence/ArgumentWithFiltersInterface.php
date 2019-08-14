<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\FilterInterface;

interface ArgumentWithFiltersInterface
{
    /**
     * @param \Spotman\Defence\Filter\FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter): void;

    /**
     * @return \Spotman\Defence\Filter\FilterInterface[]
     */
    public function getFilters(): array;
}
