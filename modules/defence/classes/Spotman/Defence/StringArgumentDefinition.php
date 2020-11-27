<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\StringFilter;

class StringArgumentDefinition extends SingleArgumentDefinition
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name)
    {
        parent::__construct($name, self::TYPE_STRING);

        $this->addFilter(new StringFilter());
    }
}
