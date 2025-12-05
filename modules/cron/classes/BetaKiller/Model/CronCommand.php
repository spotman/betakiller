<?php

declare(strict_types=1);

namespace BetaKiller\Model;

final class CronCommand extends \ORM implements CronCommandInterface
{
    public const DB_GROUP = CronLog::DB_GROUP;

    public const COL_NAME   = 'name';
    public const COL_CMD    = 'cmd';
    public const COL_PARAMS = 'params';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_db_group   = self::DB_GROUP;
        $this->_table_name = 'cron_commands';

        $this->serialize_columns([
            self::COL_PARAMS,
        ]);
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
        $this->set(self::COL_PARAMS, $value ?: null);

        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $raw = $this->get(self::COL_PARAMS);

        return $raw ?? [];
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
