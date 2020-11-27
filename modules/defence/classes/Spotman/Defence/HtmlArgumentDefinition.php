<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\HtmlFilter;

class HtmlArgumentDefinition extends SingleArgumentDefinition
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name)
    {
        parent::__construct($name, self::TYPE_HTML);

        $this->addFilter(new HtmlFilter());
    }
}
