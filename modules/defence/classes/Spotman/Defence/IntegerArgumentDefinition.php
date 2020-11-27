<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\IntegerFilter;

class IntegerArgumentDefinition extends SingleArgumentDefinition
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name)
    {
        parent::__construct($name, self::TYPE_INTEGER);

        $this->addFilter(new IntegerFilter);
    }
}
