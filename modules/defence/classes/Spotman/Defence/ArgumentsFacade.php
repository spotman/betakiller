<?php
declare(strict_types=1);

namespace Spotman\Defence;

class ArgumentsFacade
{
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
            throw new \InvalidArgumentException(
                \sprintf('Unnecessary arguments in a call, "%s" are allowed only',
                    implode('", "', \array_keys($namedArguments)))
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

        // Using named arguments already, skip processing
        if (\is_string(key($requestArguments))) {
            return $requestArguments;
        }

        $namedArguments = [];

        foreach ($definition->getArguments() as $position => $argument) {
            $name = $argument->getName();

            if (array_key_exists($position, $requestArguments)) {
                $namedArguments[$name] = $requestArguments[$position];
            } elseif ($argument->isOptional()) {
                $namedArguments[$name] = $argument->getDefaultValue();
            } else {
                throw new \InvalidArgumentException('Missing argument ":name"', [
                    ':name' => $name,
                ]);
            }
        }

        return $namedArguments;
    }

    /**
     * @param \Spotman\Defence\ArgumentDefinitionInterface[] $arguments
     * @param mixed[]                                        $data
     *
     * @return mixed[]
     */
    private function processArgumentsData(array $arguments, array $data): array
    {
        $filtered = [];

        foreach ($arguments as $argument) {
            $name = $argument->getName();

            $valueExists = isset($data[$name]);

            if (!$valueExists && !$argument->isOptional()) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is required', $name));
            }

            $targetKey = $argument->isIdentity()
                ? ArgumentsInterface::IDENTITY_KEY
                : $argument->getName();

            $filtered[$targetKey] = $valueExists
                ? $this->processValue($argument, $data[$name])
                : $argument->getDefaultValue();
        }

        return $filtered;
    }

    /**
     * @param \Spotman\Defence\ArgumentDefinitionInterface $argument
     * @param mixed                                        $value
     *
     * @return array|mixed|mixed[]
     */
    private function processValue(ArgumentDefinitionInterface $argument, $value)
    {
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

        return $value;
    }

    private function processCompositeValue(CompositeArgumentDefinitionInterface $argument, $value): array
    {
        $children = $argument->getChildren();

        // Check for children definition
        if (!$children) {
            throw new \InvalidArgumentException(sprintf('Missing nested definition for "%s"', $argument->getName()));
        }

        // Check for nested data required
        if (!$value) {
            throw new \InvalidArgumentException(sprintf('Missing nested data for "%s"', $argument->getName()));
        }

        if (\is_object($value)) {
            // Cast any incoming object to array for simplicity
            $value = (array)$value;
        }

        // Check for nested data type
        if (!\is_array($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Composite value must be an array for "%s" but "%s" provided: %s',
                $argument->getName(),
                \gettype($value),
                \json_encode($value)
            ));
        }

        // Recursion for children definitions
        return $this->processArgumentsData($children, $value);
    }

    private function processCompositeArrayValue(CompositeArrayArgumentDefinitionInterface $argument, $value): array
    {
        // Check for nested data required
        if (!$value) {
            throw new \InvalidArgumentException(sprintf('Missing nested data for "%s"', $argument->getName()));
        }

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
                \json_encode($value)
            ));
        }

        $composite = $argument->getComposite();

        $output = [];

        foreach ($value as $index => $item) {
            // All items share the same composite definition
            $output[$index] = $this->processCompositeValue($composite, $item);
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
                        \json_encode($value)
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
            if (!$rule->check($value)) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Invalid value for "%s" reported by "%s" rule with data (%s) %s',
                        $argument->getName(),
                        $rule->getName(),
                        \gettype($value),
                        \json_encode($value)
                    )
                );
            }
        }
    }
}
