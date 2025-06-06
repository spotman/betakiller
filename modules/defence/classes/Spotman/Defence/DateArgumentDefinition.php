<?php

declare(strict_types=1);

namespace Spotman\Defence;

use DateTimeZone;
use Spotman\Defence\Filter\DateFilter;

class DateArgumentDefinition extends SingleArgumentDefinition
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name, DateTimeZone $tz = null)
    {
        parent::__construct($name, self::TYPE_DATE);

        $this->addFilter(new DateFilter($tz));
    }
}
