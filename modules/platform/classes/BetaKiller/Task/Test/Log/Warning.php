<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Log;

use Psr\Log\LoggerInterface;

class Warning extends \BetaKiller\Task\AbstractTask
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

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
        $this->logger->warning('Test warning from CLI');
    }
}