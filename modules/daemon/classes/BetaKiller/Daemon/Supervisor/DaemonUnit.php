<?php
declare(strict_types=1);

namespace BetaKiller\Daemon\Supervisor;

use BetaKiller\Daemon\DaemonException;
use React\ChildProcess\Process;

final class DaemonUnit implements DaemonUnitInterface
{
    public const STATUS_LOADING  = 'loading';
    public const STATUS_STARTING = 'starting';
    public const STATUS_RUNNING  = 'running';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_STOPPING = 'stopping';
    public const STATUS_STOPPED  = 'stopped';
    public const STATUS_FAILED   = 'failed';

    public const COMMAND_START   = 'start';
    public const COMMAND_STOP    = 'stop';
    public const COMMAND_DISABLE = 'disable';

    public const EVENT_STARTED  = 'started';
    public const EVENT_FINISHED = 'finished';
    public const EVENT_FAILED   = 'failed';

    private string $name;

    private string $status = self::STATUS_LOADING;

    private ?Process $process = null;

    private int $failureCounter = 0;

    /**
     * DaemonUnit constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status, array $context = []): void
    {
        $this->status = $status;
    }

    /**
     * @inheritDoc
     */
    public function inStatus(string $name): bool
    {
        return $this->status === $name;
    }

    /**
     * @inheritDoc
     */
    public function bindToProcess(Process $process): void
    {
        if ($this->process) {
            throw new DaemonException('Daemon ":name" is still bound to Process ":pid"', [
                ':name' => $this->name,
                ':pid'  => $this->process->getPid(),
            ]);
        }

        $this->process = $process;
    }

    /**
     * @inheritDoc
     */
    public function clearProcess(): void
    {
        if (!$this->process) {
            throw new DaemonException('Daemon ":name" has no bound Process', [
                ':name' => $this->name,
            ]);
        }

        if ($this->process->isRunning()) {
            throw new DaemonException('Daemon ":name" has running Process ":pid"', [
                ':name' => $this->name,
                ':pid'  => $this->process->getPid(),
            ]);
        }

        $this->process = null;
    }

    /**
     * @inheritDoc
     */
    public function hasProcess(): bool
    {
        return $this->process !== null;
    }

    /**
     * @inheritDoc
     */
    public function getProcess(): Process
    {
        return $this->process;
    }

    public function incrementFailureCounter(): void
    {
        $this->failureCounter++;
    }

    public function resetFailureCounter(): void
    {
        $this->failureCounter = 0;
    }

    public function getFailureCounter(): int
    {
        return $this->failureCounter;
    }
}
