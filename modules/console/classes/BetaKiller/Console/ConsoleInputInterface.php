<?php

declare(strict_types=1);

namespace BetaKiller\Console;

interface ConsoleInputInterface
{
    public function has(string $name): bool;

    public function getString(string $name): string;

    public function getInt(string $name): int;

    public function getBool(string $name): bool;

    public function getOptions(): array;

    public function isString(string $name): bool;

    public function isInt(string $name): bool;

    public function isBool(string $name): bool;
}
