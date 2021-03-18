<?php

class Migration1616072339_Add_Users_Is_Phone_Verified extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1616072339;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Add_users_is_phone_verified';
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
        if (!$this->tableHasColumn('users', 'is_phone_verified')) {
            $this->runSql("ALTER TABLE `users` ADD `is_phone_verified` tinyint unsigned NOT NULL DEFAULT '0' AFTER `phone`;");
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

} // End Migration1616072339_Add_Users_Is_Phone_Verified
