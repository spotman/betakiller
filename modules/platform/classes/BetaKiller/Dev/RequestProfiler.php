<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\ServerRequestHelper;
use DebugBar\DebugBar;
use Psr\Http\Message\ServerRequestInterface;

class RequestProfiler
{
    private const TIME_DATA = 'time';

    /**
     * @var \DebugBar\DataCollector\TimeDataCollector
     */
    private $collector;

    public function enable(DebugBar $debugBar): void
    {
        if ($this->collector) {
            throw new \LogicException('DebugBar is enabled already');
        }

        if (!$debugBar->hasCollector(self::TIME_DATA)) {
            throw new \LogicException('RequestProfiler requires DebugBar TimeDataCollector');
        }

        $this->collector = $debugBar->getCollector(self::TIME_DATA);

        $this->startupMeasure();
    }

    public function start(string $label): string
    {
        // Skip profiling when DebugBar was not initialized
        if (!$this->collector) {
            return '';
        }

        $id = $this->generateId();
        $this->collector->startMeasure($id, $label);

        return $id;
    }

    public function stop(string $id, array $params = null): void
    {
        // Skip profiling when DebugBar was not initialized
        if ($this->collector && $id) {
            $this->collector->stopMeasure($id, $params ?? []);
        }
    }

    public static function begin(ServerRequestInterface $request, string $label): array
    {
        $id = ServerRequestHelper::getProfiler($request)->start($label);

        return [$request, $id];
    }

    public static function end(array $pack): void
    {
        /** @var ServerRequestInterface $request */
        [$request, $id] = $pack;

        ServerRequestHelper::getProfiler($request)->stop($id);
    }

    private function startupMeasure(): void
    {
        $start = $this->collector->getRequestStartTime();
        $end = microtime(true);

        $this->collector->addMeasure('Startup', $start, $end);
    }

    private function generateId(): string
    {
        return \base64_encode(\microtime());
    }
}
