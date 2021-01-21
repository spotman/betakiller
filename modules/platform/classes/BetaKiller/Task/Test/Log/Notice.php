<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Log;

use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class Notice extends AbstractTask
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
        parent::__construct();

        $this->logger = $logger;
    }

    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->logger->notice('Test notice from CLI');
    }
}
