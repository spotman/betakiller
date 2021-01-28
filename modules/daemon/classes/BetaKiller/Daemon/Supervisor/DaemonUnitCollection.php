<?php
declare(strict_types=1);

namespace BetaKiller\Daemon\Supervisor;

use BetaKiller\Daemon\DaemonException;

final class DaemonUnitCollection implements DaemonUnitCollectionInterface
{
    /**
     * @var \BetaKiller\Daemon\Supervisor\DaemonUnitInterface[]
     */
    private array $units = [];

    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        return isset($this->units[$name]);
    }

    /**
     * @inheritDoc
     */
    public function add(DaemonUnitInterface $unit): void
    {
        $name = $unit->getName();

        if ($this->has($name)) {
            throw new DaemonException('Daemon unit ":name" is already exists', [
                ':name' => $name,
            ]);
        }

        $this->units[$name] = $unit;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): DaemonUnitInterface
    {
        if (!$this->has($name)) {
            throw new DaemonException('Daemon unit ":name" is not exists', [
                ':name' => $name,
            ]);
        }

        return $this->units[$name];
    }

    /**
     * @inheritDoc
     */
    public function getRunning(): array
    {
        return $this->getInStatus(DaemonUnit::STATUS_RUNNING);
    }

    /**
     * @inheritDoc
     */
    public function getFailed(): array
    {
        return $this->getInStatus(DaemonUnit::STATUS_FAILED);
    }

    /**
     * @inheritDoc
     */
    public function getStopped(): array
    {
        return $this->getInStatus(DaemonUnit::STATUS_STOPPED);
    }

    private function getInStatus(string $status): array
    {
        return \array_filter($this->units, static function (DaemonUnitInterface $unit) use ($status) {
            return $unit->inStatus($status);
        });
    }
}
