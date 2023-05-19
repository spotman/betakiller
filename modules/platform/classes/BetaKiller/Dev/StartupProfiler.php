<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

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
}
