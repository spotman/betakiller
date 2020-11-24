<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Parameter\ParameterProviderFactoryInterface;

class ArgumentsFacade
{
    /**
     * @var \Spotman\Defence\Parameter\ParameterProviderFactoryInterface
     */
    private ParameterProviderFactoryInterface $paramProviderFactory;

    /**
     * ArgumentsFacade constructor.
     *
     * @param \Spotman\Defence\Parameter\ParameterProviderFactoryInterface $paramProviderFactory
     */
    public function __construct(ParameterProviderFactoryInterface $paramProviderFactory)
    {
        $this->paramProviderFactory = $paramProviderFactory;
    }

    /**
     * @param array                                       $requestArguments
     * @param \Spotman\Defence\DefinitionBuilderInterface $definition
     *
     * @return \Spotman\Defence\ArgumentsInterface
     */
    public function prepareArguments(
        array $requestArguments,
        DefinitionBuilderInterface $definition
    ): ArgumentsInterface {
        // Prepare named arguments based on a definition
        $namedArguments = $this->prepareNamedArguments($requestArguments, $definition);

        // Check for unnecessary arguments
        if (\count($requestArguments) > \count($namedArguments)) {
            $allowedArgs = array_map(static function (ArgumentDefinitionInterface $arg) {
                return $arg->getName();
            }, $definition->getArguments());

            // More details for named arguments
            if (\is_string(key($requestArguments))) {
                $requestKeys = \array_keys($requestArguments);

                throw new \InvalidArgumentException(
                    \sprintf('Unnecessary arguments in a call: "%s"',
                        implode('", "', \array_diff($requestKeys, $allowedArgs)))
                );
            }

            throw new \InvalidArgumentException(
                \sprintf('Unnecessary arguments in a call, "%s" only allowed',
                    implode('", "', \array_keys($allowedArgs)))
            );
        }

        // Filter arguments` values
        $argumentsArray = $this->processArgumentsData($definition->getArguments(), $namedArguments);

        // Return DTO
        return new Arguments($argumentsArray);
    }

    private function prepareNamedArguments(array $requestArguments, DefinitionBuilderInterface $definition): array
    {
        // Skip calls without arguments
        if (!$requestArguments) {
            return $requestArguments;
        }

        $namedArguments = [];

        foreach ($definition->getArguments() as $position => $argument) {
            $name = $argument->getName();

            // Check for named arguments first, indexed arguments next and process optional values as a fallback
            if (array_key_exists($name, $requestArguments)) {
                $namedArguments[$name] = $requestArguments[$name];
            } elseif (array_key_exists($position, $requestArguments)) {
                $namedArguments[$name] = $requestArguments[$position];
            }
        }

        return $namedArguments;
    }

    /**
     * @param \Spotman\Defence\ArgumentDefinitionInterface[]    $arguments
     * @param mixed[]                                           $data
     * @param \Spotman\Defence\ArgumentDefinitionInterface|null $parent
     *
     * @return mixed[]
     */
    private function processArgumentsData(
        array $arguments,
        array $data,
        ArgumentDefinitionInterface $parent = null
    ): array {
        $filtered = [];

        foreach ($arguments as $argument) {
            $name = $argument->getName();

            $targetKey = $argument->isIdentity()
                ? ArgumentsInterface::IDENTITY_KEY
                : $name;

            if (\array_key_exists($name, $data)) {
                // Value exists => preprocess it
                $filtered[$targetKey] = $this->processValue($argument, $data[$name], $parent);
            } elseif (!$argument->isOptional()) {
                $msg = $parent
                    ? sprintf('Key "%s.%s" is required', $parent->getName(), $name)
                    : sprintf('Key "%s" is required', $name);

                // No value, but required => warn
                throw new \InvalidArgumentException($msg);
            } elseif ($argument->hasDefaultValue()) {
                // No value, optional, has default value => use default
                $filtered[$targetKey] = $argument->getDefaultValue();
            }
        }

        return $filtered;
    }

    /**
     * @param \Spotman\Defence\ArgumentDefinitionInterface      $argument
     * @param mixed                                             $value
     *
     * @param \Spotman\Defence\ArgumentDefinitionInterface|null $parent
     *
     * @return array|mixed|mixed[]|null
     */
    private function processValue(
        ArgumentDefinitionInterface $argument,
        $value,
        ArgumentDefinitionInterface $parent = null
    ) {
        if ($value === null) {
            if ($argument->isNullable()) {
                return null;
            }

            $key = $parent
                ? $parent->getName().'.'.$argument->getName()
                : $argument->getName();

            throw new \InvalidArgumentException(sprintf('NULL value is provided for non-nullable argument "%s"', $key));
        }

        switch (true) {
            case $argument instanceof SingleArgumentDefinitionInterface:
                return $this->processSingleValue($argument, $value);

            case $argument instanceof CompositeArgumentDefinitionInterface:
                return $this->processCompositeValue($argument, $value);

            case $argument instanceof CompositeArrayArgumentDefinitionInterface:
                return $this->processCompositeArrayValue($argument, $value);

            default:
                throw new \InvalidArgumentException(sprintf(
                    'Unknown argument instance for type "%s"', $argument->getType()
                ));
        }
    }

    private function processSingleValue(SingleArgumentDefinitionInterface $argument, $value)
    {
        $value = $this->filterValue($argument, $value);

        $this->checkRules($value, $argument);

        if ($argument->isParameter()) {
            return $this->paramProviderFactory->createFor($argument)->convertValue($value);
        }

        return $value;
    }

    private function processCompositeValue(CompositeArgumentDefinitionInterface $argument, $value): ?array
    {
        $children = $argument->getChildren();

        // Check for children definition
        if (!$children) {
            throw new \InvalidArgumentException(sprintf('Missing nested definition for "%s"', $argument->getName()));
        }

        if (\is_object($value)) {
            // Cast any incoming object to array for simplicity
            $value = (array)$value;
        }

        if ($value === null) {
            return null;
        }

        // Check for nested data type
        if (!\is_array($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Composite value must be an array for "%s" but "%s" provided: %s',
                $argument->getName(),
                \gettype($value),
                \json_encode($value, JSON_THROW_ON_ERROR)
            ));
        }

        // Recursion for children definitions
        return $this->processArgumentsData($children, $value, $argument);
    }

    private function processCompositeArrayValue(CompositeArrayArgumentDefinitionInterface $argument, $value): array
    {
        if (\is_object($value)) {
            // Cast any incoming object to array for simplicity
            $value = (array)$value;
        }

        // Check for nested data type
        if (!\is_array($value)) {
            throw new \InvalidArgumentException(sprintf(
                'CompositeArray data must be an array for "%s" but "%s" provided: %s',
                $argument->getName(),
                \gettype($value),
                \json_encode($value, JSON_THROW_ON_ERROR)
            ));
        }

        $composite = $argument->getComposite();

        $output = [];

        foreach ($value as $index => $item) {
            // All items share the same composite definition
            $itemValue = $this->processCompositeValue($composite, $item);

            // Prevent null values in composite arrays
            if ($itemValue === null) {
                throw new \InvalidArgumentException(sprintf(
                    'CompositeArray data for "%s" contains NULL item at index [%d]: %s',
                    $argument->getName(),
                    $index,
                    \json_encode($value, JSON_THROW_ON_ERROR)
                ));
            }

            $output[$index] = $itemValue;
        }

        return $output;
    }

    /**
     * @param \Spotman\Defence\SingleArgumentDefinitionInterface $argument
     * @param mixed                                              $value
     *
     * @return mixed
     */
    private function filterValue(SingleArgumentDefinitionInterface $argument, $value)
    {
        $filters = $argument->getFilters();

        if (!$filters) {
            throw new \LogicException(
                \sprintf('At least one filter needs to be defined for argument "%s"', $argument->getName())
            );
        }

        foreach ($filters as $filter) {
            try {
                $value = $filter->apply($value);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Invalid value for "%s" after "%s" filter with data (%s) %s',
                        $argument->getName(),
                        $filter->getName(),
                        \gettype($value),
                        \json_encode($value, JSON_THROW_ON_ERROR)
                    ),
                    $e->getCode(),
                    $e
                );
            }
        }

        return $value;
    }

    /**
     * @param mixed                                              $value
     * @param \Spotman\Defence\SingleArgumentDefinitionInterface $argument
     *
     * @throws \InvalidArgumentException
     */
    private function checkRules($value, SingleArgumentDefinitionInterface $argument): void
    {
        foreach ($argument->getRules() as $rule) {
            // Allow empty values in optional arguments
            if (empty($value) && $argument->isOptional()) {
                break;
            }

            if (!$rule->check($value)) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Invalid value for "%s" reported by "%s" rule with data (%s) %s',
                        $argument->getName(),
                        $rule->getName(),
                        \gettype($value),
                        \json_encode($value, JSON_THROW_ON_ERROR)
                    )
                );
            }
        }
    }
}
