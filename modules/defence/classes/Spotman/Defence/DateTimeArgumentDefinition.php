<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\DateTimeFilter;

class DateTimeArgumentDefinition extends SingleArgumentDefinition
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name)
    {
        parent::__construct($name, self::TYPE_DATETIME);

        $this->addFilter(new DateTimeFilter());
    }
}
