<?php

class Migration1548692326_Increase_Notification_Log_Result_Size extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1548692326;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Increase_notification_log_result_size';
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
        $this->runSql("ALTER TABLE `notification_log` CHANGE `result` `result` text COLLATE 'utf8_unicode_ci' NULL AFTER `body`;");
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1548692326_Increate_Notification_Log_Result_Size
