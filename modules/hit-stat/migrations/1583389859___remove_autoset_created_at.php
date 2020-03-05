<?php

class Migration1583389859_Remove_Autoset_Created_At extends Migration
{

    /**
     * Returns migration ID
     *
     * @return integer
     */
    public function id(): int
    {
        return 1583389859;
    }

    /**
     * Returns migration name
     *
     * @return string
     */
    public function name(): string
    {
        return 'Remove_autoset_created_at';
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
        $this->runSql('ALTER TABLE `stat_hits` CHANGE `created_at` `created_at` timestamp NOT NULL AFTER `session_token`;');
    }

    /**
     * Removes migration
     *
     * @return void
     */
    public function down(): void
    {

    }

} // End Migration1583389859_Remove_Autoset_Created_At
