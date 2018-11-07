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
        $argumentsArray = $this->processData($namedArguments, $definition);

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
     * @param array                                       $data
     * @param \Spotman\Defence\DefinitionBuilderInterface $definition
     *
     * @return array
     */
    private function processData(array $data, DefinitionBuilderInterface $definition): array
    {
        $filtered = [];

        foreach ($definition->getArguments() as $argument) {
            $name = $argument->getName();

            $valueExists = isset($data[$name]);

            if (!$valueExists && !$argument->isOptional()) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is required', $name));
            }

            $targetKey = $argument->isIdentity()
                ? ArgumentsInterface::IDENTITY_KEY
                : $name;

            $filtered[$targetKey] = $valueExists
                ? $this->processValue($data[$name], $argument)
                : $argument->getDefaultValue();
        }

        return $filtered;
    }

    private function processValue($value, ArgumentDefinitionInterface $argument)
    {
        $value = $this->filterValue($value, $argument);

        $this->checkRules($value, $argument);

        return $value;
    }

    /**
     * @param                                              $value
     * @param \Spotman\Defence\ArgumentDefinitionInterface $argument
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    private function filterValue($value, ArgumentDefinitionInterface $argument)
    {
        $counter = 0;

        foreach ($argument->getFilters() as $filter) {
            $value = $filter->apply($value);

            if ($value === null) {
                throw new \InvalidArgumentException(
                    \sprintf('Invalid value for "%s" after "%s" filter', $argument->getName(), $filter->getName())
                );
            }

            $counter++;
        }

        if (!$counter) {
            throw new \LogicException(
                \sprintf('At least one filter needs to be defined for argument "%s"', $argument->getName())
            );
        }

        return $value;
    }

    /**
     * @param                                              $value
     * @param \Spotman\Defence\ArgumentDefinitionInterface $argument
     *
     * @throws \InvalidArgumentException
     */
    private function checkRules($value, ArgumentDefinitionInterface $argument): void
    {
        foreach ($argument->getRules() as $rule) {
            if (!$rule->check($value)) {
                throw new \InvalidArgumentException(
                    \sprintf('Invalid value for "%s" reported by "%s"', $argument->getName(), $rule->getName())
                );
            }
        }
    }
}
