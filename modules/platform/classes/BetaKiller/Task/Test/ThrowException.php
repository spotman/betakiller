<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Exception\ServerErrorHttpException;
use BetaKiller\Task\AbstractTask;

class ThrowException extends AbstractTask
{
    public function defineOptions(): array
    {
        return [
            'message' => null,
        ];
    }

    public function run(): void
    {
        $message = $this->getOption('message', false);

        throw new ServerErrorHttpException( $message ?: 'Test CLI exceptions handling');
    }
}
