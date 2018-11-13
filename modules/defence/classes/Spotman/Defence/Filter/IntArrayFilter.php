<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

class IntArrayFilter extends AbstractScalarArrayFilter
{
    /**
     * IntArrayFilter constructor.
     */
    public function __construct()
    {
        parent::__construct(new IntegerFilter);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'intArray';
    }
}
