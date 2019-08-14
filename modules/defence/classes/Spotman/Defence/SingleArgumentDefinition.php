<?php
declare(strict_types=1);

namespace Spotman\Defence;

class SingleArgumentDefinition extends AbstractArgumentDefinition implements SingleArgumentDefinitionInterface
{
    use ArgumentWithRulesTrait;
    use ArgumentWithFiltersTrait;
}
