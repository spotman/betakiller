<?php

declare(strict_types=1);

namespace BetaKiller\Console;

interface ConsoleOptionBuilderInterface
{
    public function bool(string $name): ConsoleOptionInterface;

    public function int(string $name): ConsoleOptionInterface;

    public function string(string $name): ConsoleOptionInterface;
}
