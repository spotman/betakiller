<?php

declare(strict_types=1);

namespace BetaKiller\Monitoring;

final readonly class NoOpMetricsCollector implements MetricsCollectorInterface
{
    public function single(string $variable, float|int $value): void
    {
        // No op
    }

    public function continuos(string $variable, int|float $value): void
    {
        // No op
    }

    public function counter(string $variable, int $value): void
    {
        // No op
    }

    public function increment(string $variable): void
    {
        // No op
    }

    public function decrement(string $variable): void
    {
        // No op
    }

    public function timing(string $variable, int|float $time): void
    {
        // No op
    }

    public function flush(): void
    {
        // No op
    }
}
