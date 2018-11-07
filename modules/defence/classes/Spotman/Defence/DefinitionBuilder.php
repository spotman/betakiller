<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\ArrayFilter;
use Spotman\Defence\Filter\BooleanFilter;
use Spotman\Defence\Filter\EmailFilter;
use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Filter\HtmlFilter;
use Spotman\Defence\Filter\IdentityFilter;
use Spotman\Defence\Filter\IntegerFilter;
use Spotman\Defence\Filter\LowercaseFilter;
use Spotman\Defence\Filter\StringFilter;
use Spotman\Defence\Filter\UppercaseFilter;
use Spotman\Defence\Rule\CountBetweenRule;
use Spotman\Defence\Rule\DefinitionRuleInterface;
use Spotman\Defence\Rule\PositiveIntegerRule;

class DefinitionBuilder implements DefinitionBuilderInterface
{
    /**
     * @var \Spotman\Defence\ArgumentDefinitionInterface[]
     */
    private $arguments;

    /**
     * @var \Spotman\Defence\ArgumentDefinitionInterface|null
     */
    private $last;

    public function identity(string $name = null): DefinitionBuilderInterface
    {
        return $this
            ->addArgument($name ?? 'id', ArgumentDefinitionInterface::TYPE_IDENTITY)
            ->addFilter(new IdentityFilter);
    }

    public function int(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addArgument($name, ArgumentDefinitionInterface::TYPE_INTEGER)
            ->addFilter(new IntegerFilter);
    }

    public function string(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addArgument($name, ArgumentDefinitionInterface::TYPE_STRING)
            ->addFilter(new StringFilter);
    }

    public function email(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addArgument($name, ArgumentDefinitionInterface::TYPE_EMAIL)
            ->addFilter(new EmailFilter)
            ->lowercase();
    }

    public function html(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addArgument($name, ArgumentDefinitionInterface::TYPE_HTML)
            ->addFilter(new HtmlFilter);
    }

    public function bool(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addArgument($name, ArgumentDefinitionInterface::TYPE_BOOLEAN)
            ->addFilter(new BooleanFilter);
    }

    public function array(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addArgument($name, ArgumentDefinitionInterface::TYPE_ARRAY)
            ->addFilter(new ArrayFilter);
    }

    /**
     * Mark last argument as optional with null as a default value
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function optional(): DefinitionBuilderInterface
    {
        $this->getLastArgument()->markAsOptional();

        return $this;
    }

    /**
     * Set default value for last argument
     *
     * @param $value
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function default($value): DefinitionBuilderInterface
    {
        $this->getLastArgument()->setDefaultValue($value);

        return $this;
    }

    /**
     * Retrieve defined arguments
     *
     * @return \Spotman\Defence\ArgumentDefinitionInterface[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Filter helpers
     */

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function lowercase(): DefinitionBuilderInterface
    {
        return $this->addFilter(new LowercaseFilter);
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function uppercase(): DefinitionBuilderInterface
    {
        return $this->addFilter(new UppercaseFilter);
    }

    /**
     * Rules helpers
     */

    /**
     * @param int $min
     * @param int $max
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function countBetween(int $min, int $max): DefinitionBuilderInterface
    {
        return $this->addRule(new CountBetweenRule($min, $max));
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function positive(): DefinitionBuilderInterface
    {
        return $this->addRule(new PositiveIntegerRule);
    }

    private function addArgument(string $name, string $type): self
    {
        foreach ($this->arguments as $arg) {
            if ($arg->getName() === $name) {
                throw new \DomainException(\sprintf('Duplicate argument "%s"', $name));
            }
        }

        $this->arguments[] = $this->last = new ArgumentDefinition($name, $type);

        return $this;
    }

    private function addRule(DefinitionRuleInterface $rule): self
    {
        $argument = $this->getLastArgument();

        $this->checkGuardIsAllowed($rule, $argument);

        $argument->addRule($rule);

        return $this;
    }

    private function addFilter(FilterInterface $filter): self
    {
        $argument = $this->getLastArgument();

        $this->checkGuardIsAllowed($filter, $argument);

        $argument->addFilter($filter);

        return $this;
    }

    private function checkGuardIsAllowed(GuardInterface $guard, ArgumentDefinitionInterface $argument): void
    {
        $type    = $argument->getType();
        $allowed = $guard->getArgumentTypes();

        if (!\in_array($type, $allowed, true)) {
            throw new \DomainException(sprintf(
                '"%s" may be applied to these argument types only: "%s"',
                \get_class($guard),
                \implode('", "', $allowed)
            ));
        }
    }

    private function getLastArgument(): ArgumentDefinitionInterface
    {
        if (!$this->last) {
            throw new \LogicException('No argument defined yet, can not apply guard');
        }

        return $this->last;
    }
}
