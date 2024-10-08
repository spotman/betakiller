<?php

use BetaKiller\Migration\AbstractMigration;

class Migration1579853689_Add_Cron_Log_Params extends AbstractMigration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1579853689;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_cron_log_params';
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
        if (!$this->tableHasColumn('cron_log', 'params')) {
            $this->runSql("ALTER TABLE `cron_log` ADD `params` varchar(255) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `name`;");
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

} // End Migration1579853689_Add_Cron_Log_Params
