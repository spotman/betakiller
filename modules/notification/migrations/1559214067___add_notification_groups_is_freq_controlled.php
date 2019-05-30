<?php

class Migration1559214067_Add_Notification_Groups_Is_Freq_Controlled extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1559214067;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_notification_groups_is_freq_controlled';
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
        if (!$this->tableHasColumn('notification_groups', 'is_freq_controlled')) {
            $this->runSql("ALTER TABLE `notification_groups` ADD `is_freq_controlled` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `is_system`;");
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

} // End Migration1559214067_Add_Notification_Groups_Is_Freq_Controlled
