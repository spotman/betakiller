<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\TextFilter;

class TextArgumentDefinition extends SingleArgumentDefinition
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name)
    {
        parent::__construct($name, self::TYPE_TEXT);

        $this->addFilter(new TextFilter());
    }
}
