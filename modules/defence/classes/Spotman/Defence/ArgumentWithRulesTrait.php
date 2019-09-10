<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Rule\DefinitionRuleInterface;

trait ArgumentWithRulesTrait
{
    /**
     * @var \Spotman\Defence\Rule\DefinitionRuleInterface[]
     */
    private $rules = [];

    /**
     * @param \Spotman\Defence\Rule\DefinitionRuleInterface $rule
     */
    public function addRule(DefinitionRuleInterface $rule): void
    {
        $name = $rule->getName();

        if (isset($this->rules[$name])) {
            throw new \LogicException(sprintf('Duplicate rule "%s" for argument "%s"', $name, $this->getName()));
        }

        $this->rules[$name] = $rule;
    }

    /**
     * @return \Spotman\Defence\Rule\DefinitionRuleInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

}