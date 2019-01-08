<?php

class Migration1546866265_Allow_Empty_Body_In_Notification_Log extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1546866265;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Allow_empty_body_in_notification_log';
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
        $this->runSql("ALTER TABLE `notification_log` CHANGE `body` `body` text COLLATE 'utf8_unicode_ci' NULL AFTER `subject`;");
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1546866265_Allow_Empty_Body_In_Notification_Log
