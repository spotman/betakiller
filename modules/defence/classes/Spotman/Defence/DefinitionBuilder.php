<?php
declare(strict_types=1);

namespace Spotman\Defence;

use DateTimeZone;
use Spotman\Defence\Filter\FilterInterface;
use Spotman\Defence\Filter\LowercaseFilter;
use Spotman\Defence\Filter\UppercaseFilter;
use Spotman\Defence\Rule\DefinitionRuleInterface;
use Spotman\Defence\Rule\LengthBetweenRule;
use Spotman\Defence\Rule\MaxLengthRule;
use Spotman\Defence\Rule\MinLengthRule;
use Spotman\Defence\Rule\PositiveNumberRule;
use Spotman\Defence\Rule\RegexRule;
use Spotman\Defence\Rule\WhitelistRule;

class DefinitionBuilder implements DefinitionBuilderInterface
{
    /**
     * @var \Spotman\Defence\DefinitionCollectionInterface
     */
    private DefinitionCollectionInterface $collection;

    /**
     * @var \Spotman\Defence\ArgumentDefinitionInterface|null
     */
    private ?ArgumentDefinitionInterface $last = null;

    /**
     * @var \Spotman\Defence\DefinitionCollectionInterface[]
     */
    private array $stack = [];

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
        return $this->addArgument(new IdentityArgumentDefinition($name));
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
        return $this->addArgument(new IntegerArgumentDefinition($name));
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
        return $this->addArgument(new FloatArgumentDefinition($name));
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
        return $this->addArgument(new StringArgumentDefinition($name));
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
        return $this->addArgument(new EmailArgumentDefinition($name));
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
        return $this->addArgument(new TextArgumentDefinition($name));
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
        return $this->addArgument(new HtmlArgumentDefinition($name));
    }

    /**
     * Define datetime argument (string convert to DateTimeImmutable)
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function datetime(string $name): DefinitionBuilderInterface
    {
        return $this->addArgument(new DateTimeArgumentDefinition($name));
    }

    /**
     * @inheritDoc
     */
    public function date(string $name, DateTimeZone $tz = null): DefinitionBuilderInterface
    {
        return $this->addArgument(new DateArgumentDefinition($name, $tz));
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
        return $this->addArgument(new BooleanArgumentDefinition($name));
    }

    /**
     * @inheritDoc
     */
    public function param(string $name, string $fqcn): DefinitionBuilderInterface
    {
        return $this->addArgument(new ParameterArgumentDefinition($name, $fqcn));
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
        return $this->addArgument(new SingleArrayArgumentDefinition($name, new IntegerArgumentDefinition($name)));
    }

    /**
     * Define indexed array of floats
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function floatArray(string $name): DefinitionBuilderInterface
    {
        return $this->addArgument(new SingleArrayArgumentDefinition($name, new FloatArgumentDefinition($name)));
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
        return $this->addArgument(new SingleArrayArgumentDefinition($name, new StringArgumentDefinition($name)));
    }

    /**
     * @inheritDoc
     */
    public function paramArray(string $name, string $fqcn): DefinitionBuilderInterface
    {
        return $this->addArgument(new SingleArrayArgumentDefinition(
            $name,
            new ParameterArgumentDefinition($name, $fqcn))
        );
    }

    /**
     * Define indexed array of nested collections like [{}, {}, {}]
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function compositeArrayStart(string $name): DefinitionBuilderInterface
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
     * @inheritDoc
     */
    public function compositeArrayEnd(): DefinitionBuilderInterface
    {
        return $this->compositeEnd();
    }

    /**
     * Define named collection of arguments like {"name": {}}
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function compositeStart(string $name): DefinitionBuilderInterface
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
    public function compositeEnd(): DefinitionBuilderInterface
    {
        $top = \array_pop($this->stack);

        if (!$top) {
            throw new \LogicException('No nested definition found, define it with compositeStart() method');
        }

        if (!$top instanceof ArgumentDefinitionInterface) {
            throw new \LogicException(
                sprintf('Argument definition stack must contain %s only', ArgumentDefinitionInterface::class)
            );
        }

        $this->last = $top;

        return $this;
    }

    /**
     * Mark last argument as nullable
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function nullable(): DefinitionBuilderInterface
    {
        $argument = $this->getLastArgument();

        $type   = $argument->getType();
        $denied = [
            ArgumentDefinitionInterface::TYPE_SINGLE_ARRAY,
            ArgumentDefinitionInterface::TYPE_COMPOSITE_ARRAY,
        ];

        if (\in_array($type, $denied, true)) {
            throw new \DomainException(sprintf(
                'Nullable flag can not be applied to these argument types: "%s"',
                \implode('", "', $denied)
            ));
        }

        $argument->markAsNullable();

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
            throw new \LogicException('Only scalar/array types can define default value');
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
     * @inheritDoc
     */
    public function regex(string $pattern): DefinitionBuilderInterface
    {
        return $this->addRule(new RegexRule($pattern));
    }

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
        return $this->addRule(new PositiveNumberRule);
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

        if (!$argument instanceof ArgumentWithRulesInterface) {
            throw new \LogicException('Only types implementing ArgumentWithRulesInterface can define rules');
        }

        $argument->addRule($rule);

        return $this;
    }


    private function addFilter(FilterInterface $filter): self
    {
        $argument = $this->getLastArgument();

        if (!$argument instanceof ArgumentWithFiltersInterface) {
            throw new \LogicException('Only types implementing ArgumentWithFiltersInterface can define filters');
        }

        $argument->addFilter($filter);

        return $this;
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
