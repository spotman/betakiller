<?php

declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use Psr\Log\LoggerInterface;

class Sleep extends AbstractTask
{
    private const ARG_SECONDS = 'seconds';

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
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->int(self::ARG_SECONDS)->optional(3),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $seconds = $params->getInt(self::ARG_SECONDS);

        for ($i = 0; $i < $seconds; $i++) {
            sleep(1);
            $this->logger->info('Done for :value seconds', [':value' => $i + 1]);
        }
    }
}
