<?php

class Migration1577104184_Add_User_Workflow_Status_Columns extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1577104184;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_user_workflow_status_columns';
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
        if (!$this->tableHasColumn('user_statuses', 'is_start')) {
            $this->runSql("ALTER TABLE `user_statuses`
ADD `is_start` tinyint unsigned NOT NULL DEFAULT '0',
ADD `is_finish` tinyint unsigned NOT NULL DEFAULT '0' AFTER `is_start`;");
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

} // End Migration1577104184_Add_User_Workflow_Status_Columns
