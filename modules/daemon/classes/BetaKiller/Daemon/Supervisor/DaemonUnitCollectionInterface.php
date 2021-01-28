<?php
declare(strict_types=1);

namespace BetaKiller\Daemon\Supervisor;

interface DaemonUnitCollectionInterface
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * @param \BetaKiller\Daemon\Supervisor\DaemonUnitInterface $unit
     *
     * @throws \BetaKiller\Daemon\DaemonException
     */
    public function add(DaemonUnitInterface $unit): void;

    /**
     * @param string $name
     *
     * @return \BetaKiller\Daemon\Supervisor\DaemonUnitInterface
     * @throws \BetaKiller\Daemon\DaemonException
     */
    public function get(string $name): DaemonUnitInterface;

    /**
     * @return \BetaKiller\Daemon\Supervisor\DaemonUnitInterface[]
     */
    public function getRunning(): array;

    /**
     * @return \BetaKiller\Daemon\Supervisor\DaemonUnitInterface[]
     */
    public function getStopped(): array;

    /**
     * @return \BetaKiller\Daemon\Supervisor\DaemonUnitInterface[]
     */
    public function getFailed(): array;
}
