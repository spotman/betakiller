<?php

declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\ServerRequestHelper;
use Database_Query;
use DebugBar\DataCollector\TimeDataCollector;
use Psr\Http\Message\ServerRequestInterface;

class DebugBarTimeDataCollector extends TimeDataCollector
{
    public function __construct(private readonly ?ServerRequestInterface $request = null)
    {
        $startedOn = $request
            ? RequestProfiler::getRequestStartTime($request)
            : null;

        parent::__construct($startedOn);
    }

    public function collect()
    {
        $this->collectStartupMeasures();
        $this->collectRequestMeasures();
        $this->collectDatabaseMeasures();

        return parent::collect();
    }

    private function collectStartupMeasures(): void
    {
        // Reduce data sent in ajax response
        if ($this->request && ServerRequestHelper::isAjax($this->request)) {
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
        if (!$this->request) {
            return;
        }

        // Add Request measures
        $requestProfiler = RequestProfiler::fetch($this->request);

        // Push request measures to DebugBar
        $this->importProfilerMeasures($requestProfiler);
    }

    private function collectDatabaseMeasures(): void
    {
        if (!Database_Query::isQueryLogEnabled()) {
            return;
        }

        foreach (Database_Query::getQueries() as $item) {
            if (!isset($item['index'], $item['start'], $item['duration'])) {
                continue;
            }

            $index     = $item['index'];
            $startedOn = (float)$item['start'];
            $duration  = (float)$item['duration'];

            $label = sprintf('SQL %s', $index);

            $this->addMeasure($label, $startedOn, $startedOn + $duration);
        }
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
