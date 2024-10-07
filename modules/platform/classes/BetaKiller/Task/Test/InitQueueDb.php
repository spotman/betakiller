<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Enqueue\Dbal\DbalContext;
use Interop\Queue\Context;

class InitQueueDb extends AbstractTask
{
    /**
     * @var \Interop\Queue\Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            // No options here
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        if (!$this->context instanceof DbalContext) {
            throw new TaskException('Wrong queue context');
        }

        $this->context->createDataBaseTable();
    }
}
