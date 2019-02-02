<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use Psr\Log\LoggerInterface;

class LogAlert extends \BetaKiller\Task\AbstractTask
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * LogAlert constructor.
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
        $this->logger->alert('Test alert');
    }
}
