<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\ServerRequestHelper;
use DebugBar\DebugBar;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

final class RequestProfiler
{
    private const DEBUGBAR_TIME_COLLECTOR = 'time';

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * @var float
     */
    private $createdAt;

    /**
     * RequestProfiler constructor.
     */
    public function __construct()
    {
        $this->createdAt = \microtime(true);
        $this->stopwatch = new Stopwatch(true);
    }

    public function transferMeasuresToDebugBar(DebugBar $debugBar): void
    {
        if (!$debugBar->hasCollector(self::DEBUGBAR_TIME_COLLECTOR)) {
            throw new \LogicException('RequestProfiler requires DebugBar TimeDataCollector');
        }

        /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
        $collector = $debugBar->getCollector(self::DEBUGBAR_TIME_COLLECTOR);

        $collector->addMeasure('Startup', $collector->getRequestStartTime(), $this->createdAt);

        // Iterate sections
        foreach ($this->stopwatch->getSections() as $section) {
            // iterate section events
            foreach ($section->getEvents() as $name => $event) {
                $start = $event->getOrigin();
                $end   = $event->getOrigin() + $event->getDuration();

                // Push measure to DebugBar
                $collector->addMeasure(
                    $name,
                    $start / 1000,
                    $end / 1000
                );
            }
        }
    }

    public function start(string $label): StopwatchEvent
    {
        return $this->stopwatch->start($label);
    }

    public function stop(StopwatchEvent $event): void
    {
        $event->stop();
    }

    public static function begin(ServerRequestInterface $request, string $label): array
    {
        $event = ServerRequestHelper::getProfiler($request)->start($label);

        return [$request, $event];
    }

    public static function end(array $pack): void
    {
        /** @var ServerRequestInterface $request */
        /** @var StopwatchEvent $event */
        [$request, $event] = $pack;

        ServerRequestHelper::getProfiler($request)->stop($event);
    }
}
