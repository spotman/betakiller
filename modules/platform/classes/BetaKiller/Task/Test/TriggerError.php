<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

class TriggerError extends \BetaKiller\Task\AbstractTask
{
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        trigger_error('Test CLI error handling', \E_USER_WARNING);
    }
}
