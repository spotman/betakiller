<?php

declare(strict_types=1);

namespace BetaKiller\Console;

interface ConsoleTaskFactoryInterface
{
    public function create(string $className): ConsoleTaskInterface;
}
