<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\EmailFilter;

class EmailArgumentDefinition extends SingleArgumentDefinition
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name)
    {
        parent::__construct($name, self::TYPE_EMAIL);

        $this->addFilter(new EmailFilter());
    }
}
