<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Log;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class Alert extends AbstractTask
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Alert constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $this->logger->alert('Test alert from CLI');
    }
}
