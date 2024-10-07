<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Exception\ServerErrorHttpException;
use BetaKiller\Task\AbstractTask;

class ThrowException extends AbstractTask
{
    private const ARG_MESSAGE = 'message';

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->string(self::ARG_MESSAGE)->optional('Test CLI exceptions handling'),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $message = $params->getString(self::ARG_MESSAGE);

        $this->throwException($message);
    }

    private function throwException(string $message): void
    {
        throw new ServerErrorHttpException($message);
    }
}
