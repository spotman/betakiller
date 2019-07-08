<?php

class Migration1562574227_Allow_Empty_User_Password extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1562574227;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Allow_empty_user_password';
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
        $this->runSql("ALTER TABLE `users` CHANGE `password` `password` varchar(64) COLLATE 'utf8_unicode_ci' NULL AFTER `username`;");
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1562574227_Allow_Empty_User_Password
