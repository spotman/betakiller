<?php

declare(strict_types=1);

namespace BetaKiller\Console;

interface ConsoleTaskLocatorInterface
{
    public function getTaskCmd(
        string $taskName,
        array $params = null,
        bool $showOutput = null,
        bool $detach = null
    ): string;
}
