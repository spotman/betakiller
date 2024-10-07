<?php

declare(strict_types=1);

namespace BetaKiller\Console;

enum ConsoleOptionType: string
{
    case Bool = 'bool';
    case Int = 'int';
    case String = 'string';
}
