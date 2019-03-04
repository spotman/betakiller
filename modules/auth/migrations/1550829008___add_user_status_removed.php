<?php

class Migration1550829008_Add_User_Status_Removed extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1550829008;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_user_status_removed';
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
        if (!$this->tableHasColumnValue('user_statuses', 'codename', 'suspended')) {
            $this->runSql("INSERT INTO `user_statuses` (`codename`) VALUES ('suspended');");
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

} // End Migration1550829008_Add_User_Status_Removed
