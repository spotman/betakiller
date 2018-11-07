<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

abstract class AbstractFilterVarFilter implements FilterInterface
{
    protected function filterVar($value, int $type, int $flags = null, array $options = null)
    {
        return \filter_var($value, $type, [
            'options' => $options ?? [],
            'flags'   => FILTER_NULL_ON_FAILURE | $flags ?? 0,
        ]);
    }
}
