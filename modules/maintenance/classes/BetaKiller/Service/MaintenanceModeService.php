<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Model\MaintenanceMode;
use BetaKiller\Model\UserInterface;
use DateTimeImmutable;

final class MaintenanceModeService
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * MaintenanceModeService constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    public function isDisplayedFor(UserInterface $user): bool
    {
        return !$user->isDeveloper();
    }

    public function isEnabled(): bool
    {
        $model = $this->fetch();

        if (!$model) {
            return false;
        }

        // Keep enabled while model file exists
        return $model->isDue();
    }

    public function getModel(): ?MaintenanceMode
    {
        return $this->fetch();
    }

    public function getEndTime(): DateTimeImmutable
    {
        $model = $this->fetch();

        if (!$model) {
            throw new \LogicException('End time can not be detected coz maintenance mode is off');
        }

        return $model->getEndsAt();
    }

    public function schedule(DateTimeImmutable $startTime, DateTimeImmutable $endTime): void
    {
        $model = $this->fetch();

        // If already in maintenance mode, then update end time and save
        if ($model) {
            $model->prolongTill($endTime);
        } else {
            $model = new MaintenanceMode($startTime, $endTime);
        }

        $this->store($model);
        // TODO Send event to all WAMP users about maintenance mode on
    }

    public function enable(\DateInterval $duration): void
    {
        $startTime = new DateTimeImmutable;
        $endTime   = $startTime->add($duration);

        $this->schedule($startTime, $endTime);
    }

    public function disable(): void
    {
        $this->delete();
    }

    private function store(MaintenanceMode $model): void
    {
        $file = $this->getFilePath();

        $data = \serialize($model);

        if (!\file_put_contents($file, $data, LOCK_EX)) {
            throw new \LogicException('Maintenance mode file can not be written');
        }

        \clearstatcache(true, $file);

        if (!\file_exists($file) || !\is_readable($file)) {
            throw new \LogicException('Maintenance mode file is not readable by web-server');
        }
    }

    private function fetch(): ?MaintenanceMode
    {
        $file = $this->getFilePath();

        \clearstatcache(true, $file);

        if (!\file_exists($file)) {
            // No file => no maintenance mode
            return null;
        }

        $data = \file_get_contents($file);

        $model = \unserialize($data, [
            DateTimeImmutable::class,
            \DateInterval::class,
        ]);

        if (!$model || !$model instanceof MaintenanceMode) {
            throw new \LogicException('Incorrect maintenance mode serialized data');
        }

        return $model;
    }

    private function delete(): void
    {
        $file = $this->getFilePath();

        if (\file_exists($file)) {
            // No file => maintenance mode mode off
            \unlink($file);
        }
    }

    private function getFilePath(): string
    {
        $name = implode('.', [
            $this->appEnv->getAppCodename(),
            $this->appEnv->getModeName(),
            'maintenance',
        ]);

        // Store file in a /tmp with prefix from project name and env
        return $this->appEnv->getTempPath().\DIRECTORY_SEPARATOR.$name;
    }
}
