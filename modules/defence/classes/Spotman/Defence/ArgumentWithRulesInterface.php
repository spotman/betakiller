<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Rule\DefinitionRuleInterface;

interface ArgumentWithRulesInterface
{
    /**
     * @param \Spotman\Defence\Rule\DefinitionRuleInterface $rule
     */
    public function addRule(DefinitionRuleInterface $rule): void;

    /**
     * @return \Spotman\Defence\Rule\DefinitionRuleInterface[]
     */
    public function getRules(): array;
}
