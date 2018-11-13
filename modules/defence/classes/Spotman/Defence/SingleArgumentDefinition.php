<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Rule\DefinitionRuleInterface;

class SingleArgumentDefinition extends AbstractArgumentDefinition implements SingleArgumentDefinitionInterface
{
    /**
     * @var \Spotman\Defence\Rule\DefinitionRuleInterface[]
     */
    private $rules = [];

    /**
     * @var \Spotman\Defence\Filter\FilterInterface[]
     */
    private $filters = [];

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

    /**
     * @param \Spotman\Defence\Filter\FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter): void
    {
        $name = $filter->getName();

        if (isset($this->rules[$name])) {
            throw new \LogicException(sprintf('Duplicate filter "%s" for argument "%s"', $name, $this->getName()));
        }

        $this->filters[$name] = $filter;
    }

    /**
     * @return \Spotman\Defence\Filter\FilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
