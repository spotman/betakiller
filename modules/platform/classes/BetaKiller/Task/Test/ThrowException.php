<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Exception\ServerErrorHttpException;

class ThrowException extends \BetaKiller\Task\AbstractTask
{
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        throw new ServerErrorHttpException('Test CLI exceptions handling');
    }
}
