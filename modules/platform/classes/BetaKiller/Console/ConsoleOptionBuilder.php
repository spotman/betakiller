<?php

declare(strict_types=1);

namespace BetaKiller\Console;

readonly class ConsoleOptionBuilder implements ConsoleOptionBuilderInterface
{
    public function bool(string $name): ConsoleOptionInterface
    {
        return $this->createOption($name, ConsoleOptionType::Bool)->optional(false);
    }

    public function int(string $name): ConsoleOptionInterface
    {
        return $this->createOption($name, ConsoleOptionType::Int);
    }

    public function string(string $name): ConsoleOptionInterface
    {
        return $this->createOption($name, ConsoleOptionType::String);
    }

    private function createOption(string $name, ConsoleOptionType $type): ConsoleOption
    {
        return new ConsoleOption($name, $type);
    }
}
