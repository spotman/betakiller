<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use DateTimeImmutable;

final class CronCommand extends \ORM implements CronCommandInterface
{
    public const COL_NAME       = 'name';
    public const COL_CMD     = 'cmd';
    public const COL_PARAMS     = 'params';

    private static bool $tablesChecked = false;

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_db_group   = 'cron';
        $this->_table_name = 'cron_commands';

        $this->createTablesIfNotExists();

        $this->serialize_columns([
            self::COL_PARAMS,
        ]);
    }

    private function createTablesIfNotExists(): void
    {
        if (!static::$tablesChecked) {
            $this->enableAutoVacuum();
            $this->createCronLogTableIfNotExists();
            static::$tablesChecked = true;
        }
    }

    private function createCronLogTableIfNotExists(): void
    {
        \DB::query(\Database::SELECT, 'CREATE TABLE IF NOT EXISTS `cron_commands` (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR(32) NOT NULL,
            params VARCHAR(255) NOT NULL,
            cmd VARCHAR(255) UNIQUE NOT NULL
        );')->execute($this->_db_group);
    }

    private function enableAutoVacuum(): void
    {
        \DB::query(\Database::SELECT, 'PRAGMA auto_vacuum = FULL')->execute($this->_db_group);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CronCommandInterface
     */
    public function setName(string $value): CronCommandInterface
    {
        $this->set(self::COL_NAME, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->get(self::COL_NAME);
    }

    /**
     * @param array $value
     *
     * @return \BetaKiller\Model\CronCommandInterface
     */
    public function setParams(array $value): CronCommandInterface
    {
        $this->set(self::COL_PARAMS, $value);

        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return (array)$this->get(self::COL_PARAMS);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CronCommandInterface
     */
    public function setCmd(string $value): CronCommandInterface
    {
        $this->set(self::COL_CMD, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getCmd(): string
    {
        return (string)$this->get(self::COL_CMD);
    }
}
