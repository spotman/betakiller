<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Rule\DefinitionRuleInterface;

interface ParameterArgumentDefinitionInterface extends SingleArgumentDefinitionInterface
{
    public function getCodename(): string;
}
