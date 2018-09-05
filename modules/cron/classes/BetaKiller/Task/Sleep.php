<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use Psr\Log\LoggerInterface;

class Sleep extends AbstractTask
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Sleep constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        return [
            'seconds' => 3,
        ];
    }

    public function run(): void
    {
        $seconds = (int)$this->getOption('seconds');

        for ($i = 0; $i < $seconds; $i++) {
            sleep(1);
            $this->logger->info('Done for :value seconds', [':value' => $i + 1]);
        }
    }
}
