<?php

class Migration1577185079_Remove_Notification_Frequency_Cron_Expr extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1577185079;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_notification_frequency_cron_expr';
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
        if ($this->tableHasColumn('notification_frequencies', 'cron_expression')) {
            $this->runSql('ALTER TABLE `notification_frequencies` DROP `cron_expression`;');
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

} // End Migration1577185079_Remove_Notification_Frequency_Cron_Expr
