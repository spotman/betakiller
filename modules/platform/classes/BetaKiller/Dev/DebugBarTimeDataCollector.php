<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\ServerRequestHelper;
use DebugBar\DataCollector\TimeDataCollector;
use Psr\Http\Message\ServerRequestInterface;

class DebugBarTimeDataCollector extends TimeDataCollector
{
    public function __construct(private readonly ServerRequestInterface $request)
    {
        parent::__construct(RequestProfiler::getRequestStartTime($request));
    }

    public function collect()
    {
        $this->collectStartupMeasures();
        $this->collectRequestMeasures();

        return parent::collect();
    }

    private function collectStartupMeasures(): void
    {
        // Reduce data sent in ajax response
        if (ServerRequestHelper::isAjax($this->request)) {
            return;
        }

        // Add startup measures
        $startupProfiler = StartupProfiler::getInstance();

        // Push init measure
        $this->addMeasure('Boot', $this->getRequestStartTime(), $startupProfiler->getCreatedAt());

        // Push startup measures to DebugBar
        $this->importProfilerMeasures($startupProfiler);
    }

    private function collectRequestMeasures(): void
    {
        // Add Request measures
        $requestProfiler = RequestProfiler::fetch($this->request);

        // Push request measures to DebugBar
        $this->importProfilerMeasures($requestProfiler);
    }

    private function importProfilerMeasures(AbstractProfiler $profiler): void
    {
        // Iterate sections
        foreach ($profiler->getStopwatchSections() as $section) {
            // iterate section events
            foreach ($section->getEvents() as $name => $event) {
                $start = $event->getOrigin();
                $end   = $start + $event->getDuration();

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
