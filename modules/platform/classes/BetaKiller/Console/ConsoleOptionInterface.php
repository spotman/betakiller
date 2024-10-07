<?php

declare(strict_types=1);

namespace BetaKiller\Console;

interface ConsoleOptionInterface
{
    public function required(): ConsoleOptionInterface;

    public function optional(string|int|bool $defaultValue = null): ConsoleOptionInterface;
    public function label(string $label): ConsoleOptionInterface;

    public function getName(): string;

    public function isBool(): bool;

    public function isInt(): bool;

    public function isString(): bool;

    public function isRequired(): bool;

    /**
     * @return bool|int|string|null
     */
    public function getDefaultValue(): bool|int|string|null;

    /**
     * @return string
     */
    public function getLabel(): string;
}
