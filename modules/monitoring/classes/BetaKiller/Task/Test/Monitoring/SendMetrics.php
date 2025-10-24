<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Monitoring;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Monitoring\MetricsCollectorInterface;
use Psr\Log\LoggerInterface;

final readonly class SendMetrics implements ConsoleTaskInterface
{
    private const ARG_FLUSH = 'flush';

    public function __construct(private MetricsCollectorInterface $metrics, private LoggerInterface $logger)
    {
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->bool(self::ARG_FLUSH)->optional(false),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $this->logger->info('Collector class is :class', [
            ':class' => $this->metrics::class,
        ]);

        $this->metrics->single('test.single', 100);
        $this->metrics->continuos('test.continuos', 200);
        $this->metrics->counter('test.counter', 300);
        $this->metrics->increment('test.counter');
        $this->metrics->decrement('test.counter');
        $this->metrics->timing('test.timing', 500);

        if ($params->getBool(self::ARG_FLUSH)) {
            $this->metrics->flush();
        }

        $this->logger->info('Metrics sent');
    }
}
