<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

abstract class AbstractDaemon implements DaemonInterface
{
    public const STARTUP_TIMEOUT  = 5;
    public const SHUTDOWN_TIMEOUT = 5;

    private int $processingCounter = 0;

    /**
     * @inheritDoc
     */
    public function isIdle(): bool
    {
        return $this->processingCounter === 0;
    }

    protected function markAsIdle(): void
    {
        $this->processingCounter--;

        if ($this->processingCounter < 0) {
            throw new \LogicException(
                sprintf('AbstractDaemon::markAsProcessing() call is missing somewhere in "%s', \get_class($this))
            );
        }
    }

    protected function markAsProcessing(): void
    {
        $this->processingCounter++;

        if ($this->processingCounter > 500) {
            throw new \LogicException(
                sprintf('AbstractDaemon::markAsIdle() call is missing somewhere in "%s', \get_class($this))
            );
        }
    }
}
