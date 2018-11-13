<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

use Spotman\Defence\GuardInterface;

interface FilterInterface extends GuardInterface
{
    /**
     * Apply current filter to provided value
     *
     * @param mixed $value
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function apply($value);
}
