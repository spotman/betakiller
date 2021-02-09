<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

interface DaemonInterface
{
    public const NAMESPACES       = ['Daemon'];
    public const SUFFIX           = 'Daemon';
    public const EXIT_CODE_FAILED = 1;
    public const EXIT_CODE_OK     = 0;

    /**
     * @param \React\EventLoop\LoopInterface $loop
     *
     * @return \React\Promise\PromiseInterface
     */
    public function startDaemon(LoopInterface $loop): PromiseInterface;

    /**
     * @param \React\EventLoop\LoopInterface $loop
     *
     * @return \React\Promise\PromiseInterface
     */
    public function stopDaemon(LoopInterface $loop): PromiseInterface;

    /**
     * Must return true if daemon is in idle mode (no pending operations)
     *
     * @return bool
     */
    public function isIdle(): bool;

    /**
     * Must return true if daemon allows to be restarted after filesystem changes
     *
     * @return bool
     */
    public function isRestartOnFsChangesAllowed(): bool;
}
