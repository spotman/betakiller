<?php

declare(strict_types=1);

namespace BetaKiller\Monitoring;

use BetaKiller\Exception\LogicException;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;

final class OpenTelemetryMetricsCollector implements MetricsCollectorInterface
{
    /**
     * @inheritDoc
     */
    public function single(string $variable, int|float $value): void
    {
        $this->getMeter()->createGauge($variable)->record($value);
    }

    /**
     * @inheritDoc
     */
    public function continuos(string $variable, int|float $value): void
    {
        $this->getMeter()->createHistogram($variable)->record($value);
    }

    /**
     * @inheritDoc
     */
    public function counter(string $variable, int $value): void
    {
        $this->getMeter()->createUpDownCounter($variable)->add($value);
    }

    /**
     * @inheritDoc
     */
    public function increment(string $variable): void
    {
        $this->getMeter()->createUpDownCounter($variable)->add(1);
    }

    /**
     * @inheritDoc
     */
    public function decrement(string $variable): void
    {
        $this->getMeter()->createUpDownCounter($variable)->add(-1);
    }

    /**
     * @inheritDoc
     */
    public function timing(string $variable, int|float $time): void
    {
        $this->getMeter()->createHistogram($variable)->record($time);
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        $this->getProvider()->forceFlush();
    }

    public function __destruct()
    {
        $this->getProvider()->shutdown();
    }

    private function getMeter(): MeterInterface
    {
        return $this->getProvider()->getMeter('metrics');
    }

    private function getProvider(): MeterProviderInterface
    {
        $provider = Globals::meterProvider();

        if (!$provider instanceof MeterProviderInterface) {
            throw new LogicException('Incorrect data type of OpenTelemetry MeterProvider');
        }

        return $provider;
    }
}
