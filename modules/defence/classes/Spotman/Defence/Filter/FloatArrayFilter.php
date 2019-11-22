<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

class FloatArrayFilter extends AbstractScalarArrayFilter
{
    /**
     * IntArrayFilter constructor.
     */
    public function __construct()
    {
        parent::__construct(new FloatFilter);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'floatArray';
    }
}
