<?php

class Migration1546872306_Add_Notification_Log_Hash extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1546872306;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_notification_log_hash';
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
        if (!$this->tableHasColumn('notification_log', 'hash')) {
            $this->runSql('ALTER TABLE `notification_log` ADD `hash` varchar(128) NOT NULL AFTER `id`;');
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

} // End Migration1546872306_Add_Notification_Log_Hash
