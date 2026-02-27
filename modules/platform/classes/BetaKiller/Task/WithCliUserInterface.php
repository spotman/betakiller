<?php

declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleTaskInterface;

/**
 * Marker for Task which requires a CLI User for processing
 * User will be injected into constructor, if there is a "UserInterface $user" argument
 */
interface WithCliUserInterface extends ConsoleTaskInterface
{
}
