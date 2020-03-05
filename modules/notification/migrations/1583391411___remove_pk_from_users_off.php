<?php

class Migration1583391411_Remove_Pk_From_Users_Off extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1583391411;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_pk_from_users_off';
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
        if ($this->tableHasColumn('notification_groups_users_off', 'id')) {
            $this->runSql('ALTER TABLE `notification_groups_users_off` DROP `id`;');
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

} // End Migration1583391411_Remove_Pk_From_Users_Off
