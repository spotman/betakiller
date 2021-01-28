<?php
declare(strict_types=1);

namespace BetaKiller\Daemon\Supervisor;

use React\ChildProcess\Process;

interface DaemonUnitInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function inStatus(string $name): bool;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $status
     * @param array  $context
     */
    public function setStatus(string $status, array $context = []): void;

    /**
     * @param \React\ChildProcess\Process $process
     */
    public function bindToProcess(Process $process): void;

    /**
     * Remove binding to Process
     */
    public function clearProcess(): void;

    /**
     * @return bool
     */
    public function hasProcess(): bool;

    /**
     * @return \React\ChildProcess\Process
     */
    public function getProcess(): Process;

//    public function onStartedEvent(callable $cb): void;
//
//    public function onFinishedEvent(callable $cb): void;
//
//    public function onFailedEvent(callable $cb): void;

    public function incrementFailureCounter(): void;

    public function resetFailureCounter(): void;

    public function getFailureCounter(): int;
}
