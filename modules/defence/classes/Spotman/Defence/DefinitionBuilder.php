<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\BooleanFilter;
use Spotman\Defence\Filter\EmailFilter;
use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Filter\FloatFilter;
use Spotman\Defence\Filter\HtmlFilter;
use Spotman\Defence\Filter\IdentityFilter;
use Spotman\Defence\Filter\IntArrayFilter;
use Spotman\Defence\Filter\IntegerFilter;
use Spotman\Defence\Filter\LowercaseFilter;
use Spotman\Defence\Filter\StringArrayFilter;
use Spotman\Defence\Filter\StringFilter;
use Spotman\Defence\Filter\TextFilter;
use Spotman\Defence\Filter\UppercaseFilter;
use Spotman\Defence\Rule\DefinitionRuleInterface;
use Spotman\Defence\Rule\LengthBetweenRule;
use Spotman\Defence\Rule\MaxLengthRule;
use Spotman\Defence\Rule\MinLengthRule;
use Spotman\Defence\Rule\PositiveIntegerRule;
use Spotman\Defence\Rule\WhitelistRule;

class DefinitionBuilder implements DefinitionBuilderInterface
{
    /**
     * @var \Spotman\Defence\DefinitionCollectionInterface
     */
    private $collection;

    /**
     * @var \Spotman\Defence\ArgumentDefinitionInterface|null
     */
    private $last;

    /**
     * @var \Spotman\Defence\DefinitionCollectionInterface[]
     */
    private $stack = [];

    /**
     * DefinitionBuilder constructor.
     */
    public function __construct()
    {
        $this->collection = new DefinitionCollection;
    }

    /**
     * Define ID argument
     *
     * @param string|null $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function identity(string $name = null): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name ?? 'id', ArgumentDefinitionInterface::TYPE_IDENTITY)
            ->addFilter(new IdentityFilter);
    }

    /**
     * Define int argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function int(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name, ArgumentDefinitionInterface::TYPE_INTEGER)
            ->addFilter(new IntegerFilter);
    }

    /**
     * Define float argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function float(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name, ArgumentDefinitionInterface::TYPE_FLOAT)
            ->addFilter(new FloatFilter);
    }

    /**
     * Define string argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function string(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name, ArgumentDefinitionInterface::TYPE_STRING)
            ->addFilter(new StringFilter);
    }

    /**
     * Define string argument containing email
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function email(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name, ArgumentDefinitionInterface::TYPE_EMAIL)
            ->addFilter(new EmailFilter);
    }

    /**
     * Define string argument containing multi-line text
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function text(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name, ArgumentDefinitionInterface::TYPE_TEXT)
            ->addFilter(new TextFilter);
    }

    /**
     * Define string argument containing HTML code
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function html(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name, ArgumentDefinitionInterface::TYPE_HTML)
            ->addFilter(new HtmlFilter);
    }

    /**
     * Define bool argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function bool(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name, ArgumentDefinitionInterface::TYPE_BOOLEAN)
            ->addFilter(new BooleanFilter);
    }

    /**
     * Define indexed array of integers
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function intArray(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name, ArgumentDefinitionInterface::TYPE_SINGLE_ARRAY)
            ->addFilter(new IntArrayFilter);
    }

    /**
     * Define indexed array of strings like ['asd', 'qwe']
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function stringArray(string $name): DefinitionBuilderInterface
    {
        return $this
            ->addSingleType($name, ArgumentDefinitionInterface::TYPE_SINGLE_ARRAY)
            ->addFilter(new StringArrayFilter);
    }

    /**
     * Define indexed array of nested collections like [{}, {}, {}]
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function compositeArray(string $name): DefinitionBuilderInterface
    {
        $composite      = new CompositeArgumentDefinition($name.'-composite');
        $compositeArray = new CompositeArrayArgumentDefinition($name, $composite);

        // Mark compositeArray as last to define optional() and default()
        $this->addArgument($compositeArray);

        // Push composite to stack so child arguments can be added
        $this->stack[] = $composite;

        return $this;
    }

    /**
     * Define named collection of arguments like {"name": {}}
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function composite(string $name): DefinitionBuilderInterface
    {
        // Create composite
        $argument = new CompositeArgumentDefinition($name);

        $this->addArgument($argument);

        $this->stack[] = $argument;

        return $this;
    }

    /**
     * End nested definition
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function endComposite(): DefinitionBuilderInterface
    {
        $top = \array_pop($this->stack);

        if (!$top) {
            throw new \LogicException('No nested definition found, define it with composite() method');
        }

        $this->last = $top;

        return $this;
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
        $last = $this->getLastArgument();

        if (!$last->isOptional()) {
            throw new \LogicException('Only optional arguments can define default value');
        }

        if (!$last->mayHaveDefaultValue()) {
            throw new \LogicException('Only scalar types can define default value');
        }

        $last->setDefaultValue($value);

        return $this;
    }

    /**
     * Retrieve defined arguments
     *
     * @return \Spotman\Defence\ArgumentDefinitionInterface[]
     */
    public function getArguments(): array
    {
        return $this->collection->getChildren();
    }

    /**
     * @return bool
     */
    public function hasArguments(): bool
    {
        return $this->collection->count() > 0;
    }

    /**
     * @param \Spotman\Defence\ArgumentsDefinitionProviderInterface $provider
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function import(ArgumentsDefinitionProviderInterface $provider): DefinitionBuilderInterface
    {
        $provider->addArgumentsDefinition($this);

        return $this;
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
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function minLength(int $min): DefinitionBuilderInterface
    {
        return $this->addRule(new MinLengthRule($min));
    }

    /**
     * @param int $max
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function maxLength(int $max): DefinitionBuilderInterface
    {
        return $this->addRule(new MaxLengthRule($max));
    }

    /**
     * @param int $min
     * @param int $max
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function lengthBetween(int $min, int $max): DefinitionBuilderInterface
    {
        return $this->addRule(new LengthBetweenRule($min, $max));
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function positive(): DefinitionBuilderInterface
    {
        return $this->addRule(new PositiveIntegerRule);
    }

    /**
     * @param array $allowed
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function whitelist(array $allowed): DefinitionBuilderInterface
    {
        return $this->addRule(new WhitelistRule($allowed));
    }

    private function addSingleType(string $name, string $type): self
    {
        $this->addArgument(new SingleArgumentDefinition($name, $type));

        return $this;
    }

    private function addArgument(ArgumentDefinitionInterface $argument): self
    {
        $this->checkArgumentExists($argument);

        $this->getCollection()->addChild($argument);

        $this->last = $argument;

        return $this;
    }

    private function checkArgumentExists(ArgumentDefinitionInterface $argument): void
    {
        $name = $argument->getName();

        foreach ($this->getCollection()->getChildren() as $arg) {
            if ($arg->getName() === $name) {
                throw new \DomainException(\sprintf('Duplicate argument "%s"', $name));
            }
        }
    }

    private function addRule(DefinitionRuleInterface $rule): self
    {
        $argument = $this->getLastArgument();

        $this->checkGuardIsAllowed($rule, $argument);

        if (!$argument instanceof SingleArgumentDefinitionInterface) {
            throw new \LogicException('Only scalar types can define rules');
        }

        $argument->addRule($rule);

        return $this;
    }

    private function addFilter(FilterInterface $filter): self
    {
        $argument = $this->getLastArgument();

        $this->checkGuardIsAllowed($filter, $argument);

        if (!$argument instanceof SingleArgumentDefinitionInterface) {
            throw new \LogicException('Only scalar types can define filters');
        }

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

    private function getParent(): ?DefinitionCollectionInterface
    {
        return end($this->stack) ?: null;
    }

    private function getCollection(): DefinitionCollectionInterface
    {
        return $this->getParent() ?: $this->collection;
    }
}
