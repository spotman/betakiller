<?php

declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleTaskInterface;

/**
 * Marker for Task which requires a CLI User for processing
 */
interface WithCliUserInterface extends ConsoleTaskInterface
{
}
