<?php

declare(strict_types=1);

namespace BetaKiller\Console;

use LogicException;
use Webmozart\Assert\Assert;

final readonly class ConsoleInput implements ConsoleInputInterface
{
    /**
     * @var array<string, string|int|bool|null>
     */
    private array $runValues;

    /**
     * @param array<string, string|int|bool|null>                  $requestData
     * @param \BetaKiller\Console\ConsoleOptionCollectionInterface $definedOptions
     *
     * @return static
     */
    public static function createFrom(array $requestData, ConsoleOptionCollectionInterface $definedOptions): self
    {
        $requestValues = [];

        // Detect request values
        foreach ($definedOptions as $option) {
            $name = $option->getName();

            $requestValue = $requestData[$name] ?? null;

            if ($requestValue === null) {
                continue;
            }

            // Validate input data types
            switch (true) {
                case $option->isString():
                    Assert::string($requestValue);
                    break;

                case $option->isInt():
                    Assert::numeric($requestValue);
                    break;

                case $option->isBool():
                    Assert::inArray($requestValue, ['true', 'false']);
                    break;

                default:
                    throw new LogicException(sprintf('Unknown Option "%s" type', $name));
            }

            // Providing option key for the option with default boolean value means "true"
            $requestValues[$name] = match (true) {
                $option->isString() => (string)$requestValue,
                $option->isInt() => (int)$requestValue,
                $option->isBool() => $requestValue !== 'false',
                default => throw new LogicException(sprintf('Unknown Option "%s" type', $name))
            };
        }

        // Check for unknown options
        foreach ($requestValues as $requestName => $requestValue) {
            if (!is_string($requestName)) {
                continue;
            }

            if (!$definedOptions->has($requestName)) {
                throw new LogicException(sprintf('Unknown Option "%s"', $requestName));
            }
        }

        return new self($definedOptions, $requestValues);
    }

    /**
     * @param \BetaKiller\Console\ConsoleOptionCollectionInterface $definedOptions
     * @param array<string, string|int|bool>                       $requestValues
     */
    private function __construct(private ConsoleOptionCollectionInterface $definedOptions, private array $requestValues)
    {
        $runValues = [];

        // Fulfill run options
        foreach ($definedOptions as $option) {
            $name = $option->getName();

            $value = $requestValues[$name] ?? null;

            if ($value === null && $option->isRequired()) {
                throw new LogicException(sprintf('Option "%s" is required', $name));
            }

            $runValues[$name] = $value ?? $option->getDefaultValue();
        }

        $this->runValues = $runValues;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->requestValues);
    }

    public function getString(string $name): string
    {
        $value = $this->getOptionValue($name);

        Assert::string($value);

        return $value;
    }

    public function getInt(string $name): int
    {
        $value = $this->getOptionValue($name);

        Assert::integer($value);

        return $value;
    }

    public function getBool(string $name): bool
    {
        $value = $this->getOptionValue($name);

        Assert::boolean($value);

        return $value;
    }

    public function getOptions(): array
    {
        return $this->runValues;
    }

    public function isString(string $name): bool
    {
        return $this->getOption($name)->isString();
    }

    public function isInt(string $name): bool
    {
        return $this->getOption($name)->isInt();
    }

    public function isBool(string $name): bool
    {
        return $this->getOption($name)->isBool();
    }

    private function getOption(string $name): ConsoleOptionInterface
    {
        $option = $this->definedOptions[$name] ?? null;

        if (!$option) {
            throw new LogicException(sprintf('Unknown Option "%s"', $name));
        }

        return $option;
    }

    private function getOptionValue(string $name): string|int|bool|null
    {
        if (!isset($this->runValues[$name])) {
            throw new LogicException(sprintf('Unknown Option "%s"', $name));
        }

        return $this->runValues[$name];
    }
}
