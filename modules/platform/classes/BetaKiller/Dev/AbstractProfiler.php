<?php

declare(strict_types=1);

namespace BetaKiller\Dev;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

abstract class AbstractProfiler
{
    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    protected Stopwatch $stopwatch;

    /**
     * RequestProfiler constructor.
     */
    public function __construct()
    {
        $this->stopwatch = new Stopwatch(true);
    }

    /**
     * @return \Symfony\Component\Stopwatch\Section[]
     */
    public function getStopwatchSections(): array
    {
        return $this->stopwatch->getSections();
    }

    public function start(string $label): StopwatchEvent
    {
        return $this->stopwatch->start($label);
    }

    public function stop(StopwatchEvent $event): void
    {
        $event->stop();
    }

    public function measure(string $label, callable $fn): mixed
    {
        $m = $this->start($label);

        $result = $fn();

        $this->stop($m);

        return $result;
    }
}
