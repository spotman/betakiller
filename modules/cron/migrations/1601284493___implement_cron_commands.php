<?php

use BetaKiller\Model\CronCommand;
use BetaKiller\Model\CronLog;

class Migration1601284493_Implement_Cron_Commands extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1601284493;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Implement_cron_commands';
	}

	/**
	 * Returns migration info
	 *
	 * @return string
	 */
	public function description(): string
	{
		return '';
	}

	/**
	 * Takes a migration
	 *
	 * @return void
	 */
	public function up(): void
	{
	    // Drop MySQL table first
        if ($this->tableExists('cron_log', 'default')) {
            $this->runSql("DROP TABLE `cron_log`;", Database::DELETE, 'default');
        }

        // Drop SQLite table (nothing meaningful there)
        if ($this->tableExists('cron_log', 'cron')) {
            $this->runSql("DROP TABLE `cron_log`;", Database::DELETE, 'cron');
        }

        // Force create of new tables
        new CronCommand;
        new CronLog;
    }

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{

	}

} // End Migration1601284493_Implement_Cron_Commands
