<?php

class Migration1573131417_Make_Username_Optional extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1573131417;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Make_username_optional';
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
        $this->runSql("ALTER TABLE `users`
CHANGE `username` `username` varchar(41) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `email`,
CHANGE `password` `password` varchar(64) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `username`,
COLLATE 'utf8mb4_unicode_ci';");
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1573131417_Make_Username_Optional
