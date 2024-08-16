<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use Symfony\Component\Stopwatch\StopwatchEvent;

final class StartupProfiler extends AbstractProfiler
{
    /**
     * @var float
     */
    private float $createdAt;

    public static function getInstance(): self
    {
        static $instance;

        return $instance ?: $instance = new self();
    }

    private function __construct()
    {
        $this->createdAt = \microtime(true);

        parent::__construct();
    }

    public function getCreatedAt(): float
    {
        return $this->createdAt;
    }

    public static function begin(string $label): StopwatchEvent
    {
        return self::getInstance()->start($label);
    }

    public static function end(StopwatchEvent $event): void
    {
        self::getInstance()->stop($event);
    }
}
