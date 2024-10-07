<?php

declare(strict_types=1);

namespace BetaKiller\Console;

class ConsoleOption implements ConsoleOptionInterface
{
    private bool $required = false;

    private string|int|bool|null $defaultValue = null;

    private string $label = '';

    public function __construct(private readonly string $name, private readonly ConsoleOptionType $type)
    {
    }

    public function required(): ConsoleOptionInterface
    {
        $this->required     = true;
        $this->defaultValue = null;

        return $this;
    }

    public function optional(string|int|bool $defaultValue = null): ConsoleOptionInterface
    {
        $this->required     = false;
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function label(string $label): ConsoleOptionInterface
    {
        $this->label = $label;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isBool(): bool
    {
        return $this->type === ConsoleOptionType::Bool;
    }

    public function isInt(): bool
    {
        return $this->type === ConsoleOptionType::Int;
    }

    public function isString(): bool
    {
        return $this->type === ConsoleOptionType::String;
    }

    public function isRequired(): bool
    {
        return $this->required ?? true;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultValue(): bool|int|string|null
    {
        return $this->defaultValue;
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
