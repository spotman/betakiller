<?php

class Migration1559573925_Add_Notification_Groups__Place extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1559573925;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_notification_groups__place';
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
        if (!$this->tableHasColumn('notification_groups', 'place')) {
            $this->runSql("ALTER TABLE `notification_groups` ADD `place` smallint unsigned NOT NULL DEFAULT '0';");
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

} // End Migration1559573925_Add_Notification_Groups__Place
