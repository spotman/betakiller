<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Rule\DefinitionRuleInterface;

interface SingleArgumentDefinitionInterface extends ArgumentDefinitionInterface, ArgumentWithRulesInterface, ArgumentWithFiltersInterface
{
}
