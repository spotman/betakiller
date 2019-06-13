<?php

class Migration1560413787_Remove_Notification_Log_From_Mysql extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1560413787;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_notification_log_from_mysql';
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
        if ($this->tableExists('notification_log')) {
            $this->runSql('DROP TABLE `notification_log`;');
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

} // End Migration1560413787_Remove_Notification_Log_From_Mysql
