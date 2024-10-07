<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Task\AbstractTask;

class TriggerError extends AbstractTask
{
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        trigger_error('Test CLI error handling', \E_USER_WARNING);
    }
}
