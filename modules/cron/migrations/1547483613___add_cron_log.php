<?php

use BetaKiller\Migration\AbstractMigration;

class Migration1547483613_Add_Cron_Log extends AbstractMigration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1547483613;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_cron_log';
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
        if (!$this->tableExists('cron_log')) {
            $this->runSql("CREATE TABLE `cron_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(32) NOT NULL,
  `cmd` varchar(255) NOT NULL,
  `result` enum('queued', 'succeeded','failed') NOT NULL,
  `queued_at` datetime,
  `started_at` datetime NULL,
  `stopped_at` datetime NULL
) ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';");
        }
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1547483613_Add_Cron_Log
