<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Rule\DefinitionRuleInterface;

class ArgumentDefinition implements ArgumentDefinitionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $optional = false;

    /**
     * @var mixed|null
     */
    private $defaultValue;

    /**
     * @var \Spotman\Defence\Rule\DefinitionRuleInterface[]
     */
    private $rules;

    /**
     * @var \Spotman\Defence\Filter\FilterInterface[]
     */
    private $filters;

    /**
     * ArgumentDefinition constructor.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        if (\in_array($type, self::ALLOWED_TYPES, true)) {
            throw new \LogicException(\sprintf('Unknown argument type "%s"', $type));
        }

        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     *
     */
    public function markAsOptional(): void
    {
        $this->optional = true;
    }

    /**
     * @param mixed $value
     */
    public function setDefaultValue($value): void
    {
        $this->defaultValue = $value;
    }

    /**
     * @param \Spotman\Defence\Rule\DefinitionRuleInterface $rule
     */
    public function addRule(DefinitionRuleInterface $rule): void
    {
        $name = $rule->getName();

        if (isset($this->rules[$name])) {
            throw new \LogicException(sprintf('Duplicate rule "%s" for argument "%s"', $name, $this->name));
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
            throw new \LogicException(sprintf('Duplicate filter "%s" for argument "%s"', $name, $this->name));
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

    /**
     * Returns true if rule defines identity argument
     *
     * @return bool
     */
    public function isIdentity(): bool
    {
        return $this->getType() === self::TYPE_IDENTITY;
    }

    /**
     * Returns true if rule defines boolean argument
     *
     * @return bool
     */
    public function isBool(): bool
    {
        return $this->getType() === self::TYPE_BOOLEAN;
    }

    /**
     * Returns true if rule defines integer argument
     *
     * @return bool
     */
    public function isInt(): bool
    {
        return $this->getType() === self::TYPE_INTEGER;
    }

    /**
     * Returns true if rule defines string argument
     *
     * @return bool
     */
    public function isString(): bool
    {
        return $this->getType() === self::TYPE_STRING;
    }

    /**
     * Returns true if rule defines a string containing email
     *
     * @return bool
     */
    public function isEmail(): bool
    {
        return $this->getType() === self::TYPE_EMAIL;
    }

    /**
     * Returns true if rule defines string containing html
     *
     * @return bool
     */
    public function isHtml(): bool
    {
        return $this->getType() === self::TYPE_HTML;
    }

    /**
     * Returns true if rule defines array argument
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->getType() === self::TYPE_ARRAY;
    }
}
