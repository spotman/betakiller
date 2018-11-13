<?php
declare(strict_types=1);

namespace Spotman\Defence\Filter;

class StringArrayFilter extends AbstractScalarArrayFilter
{
    /**
     * IntArrayFilter constructor.
     */
    public function __construct()
    {
        parent::__construct(new StringFilter);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'stringArray';
    }
}
