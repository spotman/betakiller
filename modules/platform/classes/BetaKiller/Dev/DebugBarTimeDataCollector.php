<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use DebugBar\DataCollector\TimeDataCollector;
use Psr\Http\Message\ServerRequestInterface;

class DebugBarTimeDataCollector extends TimeDataCollector
{
    public function __construct(private readonly ?ServerRequestInterface $request = null)
    {
        parent::__construct(RequestProfiler::getRequestStartTime($request));
    }

    public function collect()
    {
        // Add startup measures
        $startupProfiler = StartupProfiler::getInstance();

        // Push init measure
        $this->addMeasure('Boot', $this->getRequestStartTime(), $startupProfiler->getCreatedAt());

        // Push startup measures to DebugBar
        $this->importProfilerMeasures($startupProfiler);

        // Add Request measures
        $requestProfiler = $this->request ? RequestProfiler::fetch($this->request) : null;

        if ($requestProfiler) {
            // Push request measures to DebugBar
            $this->importProfilerMeasures($requestProfiler);
        }

        return parent::collect();
    }

    private function importProfilerMeasures(AbstractProfiler $profiler): void
    {
        // Iterate sections
        foreach ($profiler->getStopwatchSections() as $section) {
            // iterate section events
            foreach ($section->getEvents() as $name => $event) {
                $start = $event->getOrigin();
                $end   = $event->getOrigin() + $event->getDuration();

                // Push measure to DebugBar
                $this->addMeasure(
                    $name,
                    $start / 1000,
                    $end / 1000
                );
            }
        }
    }
}
