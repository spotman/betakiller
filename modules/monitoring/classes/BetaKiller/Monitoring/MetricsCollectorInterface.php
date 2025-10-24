<?php

declare(strict_types=1);

namespace BetaKiller\Monitoring;

interface MetricsCollectorInterface
{
    /**
     * Gauge-like measurement.
     *
     * @param string    $variable
     * @param int|float $value The value
     */
    public function single(string $variable, int|float $value): void;

    /**
     * Histogram-like measurement.
     *
     * @param string    $variable
     * @param int|float $value The value
     */
    public function continuos(string $variable, int|float $value): void;

    /**
     * Updates a counter by some arbitrary amount.
     *
     * @param string $variable
     * @param int    $value The amount to increment the counter by
     */
    public function counter(string $variable, int $value): void;

    /**
     * Increments a counter.
     *
     * @param string $variable
     */
    public function increment(string $variable): void;

    /**
     * Decrements a counter.
     *
     * @param string $variable
     */
    public function decrement(string $variable): void;

    /**
     * Records a timing.
     *
     * @param string    $variable
     * @param int|float $time The duration of the timing in milliseconds
     */
    public function timing(string $variable, int|float $time): void;

    /**
     * Sends the metrics to the adapter backend.
     */
    public function flush(): void;
}
