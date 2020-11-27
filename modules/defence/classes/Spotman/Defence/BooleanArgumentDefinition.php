<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\BooleanFilter;

class BooleanArgumentDefinition extends SingleArgumentDefinition
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name)
    {
        parent::__construct($name, self::TYPE_BOOLEAN);

        $this->addFilter(new BooleanFilter());
    }
}
