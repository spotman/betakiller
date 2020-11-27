<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\IdentityFilter;

class IdentityArgumentDefinition extends SingleArgumentDefinition
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name ?? 'id', self::TYPE_IDENTITY);

        $this->addFilter(new IdentityFilter);
    }
}
